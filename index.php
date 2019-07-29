<?php

/*
 * That's gonna be a great app.
 */

// Bootstrap
use \RobThree\Auth\TwoFactorAuth;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once('vendor/autoload.php');

// DB Configuration
$isDevMode = true;
$dbConfig = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/model"), $isDevMode);
$dbConn = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/db.sqlite',
);
$entityManager = EntityManager::create($dbConn, $dbConfig);

// Basic Configuration
$config = array(
    'theme' => 'default',
    'defaultPage' => 'index',
    'twigCache' => false,
    // No cache today. Sorry.
    //'twigCache' => 'cache/twig'
);

$loader = new \Twig\Loader\FilesystemLoader('view/theme/'.$config['theme'].'/template/');

$twig = new \Twig\Environment($loader, [
    'cache' => $config['twigCache'],
]);

// Array to pass to the templates
$data = array();

foreach($config as $k => $v) {
    $data[$k] = $v;
}

// Render templates by default
$render = true;

// If the above is set to false, we turn this json array
$json = array();

// A very basic router... if we can call it this way
$page = $config['defaultPage'];

if(isset($_GET['page'])) {
    $page = filter_var($_GET['page'], FILTER_SANITIZE_STRING);
}

switch($page) {
    case 'index':
        $data['heading_title'] = 'Upmind - 2f Auth';
        break;
    case 'generate':
        $render = false;

        $label = filter_var($_POST['label'], FILTER_SANITIZE_STRING);
        $name = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        $tfa = new TwoFactorAuth($label);

        // Check for existing user
        $u = $entityManager->getRepository('User')->findBy(array('name' => $name));

        if(!isset($u[0])) {
            $secret = $tfa->createSecret(160);

            // Create our user
            $new = new User();
            $new->setName($name);
            $new->setSecret($secret);
            $new->setLabel($label);

            $entityManager->persist($new);
            $entityManager->flush();
        } else {
            $secret = $u[0]->getSecret();
            $u[0]->setLabel($label);
            $entityManager->flush();
        }

        $qrCode = $tfa->getQRCodeImageAsDataUri($name, $secret);

        $json['message'] = '<img src="'.$qrCode.'"/>';
        break;
    case 'verify':
        $render = false;

        $code = filter_var($_POST['code'], FILTER_SANITIZE_STRING);
        $name = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        $u = $entityManager->getRepository('User')->findBy(array('name' => $name));

        if(!isset($u[0])) {
            $secret = "";
        } else {
            $secret = $u[0]->getSecret();
        }

        $tfa = new TwoFactorAuth();

        $realCode = $tfa->getCode($secret);

        if($tfa->verifyCode($secret, $code, 1) === true && $code == $realCode) {
            $json['message'] = 'Welcome back, <strong>'.$name.'</strong>!';
            $json['status'] = 1;
        } else {
            $json['message'] = 'The code is not correct!';
            $json['status'] = 0;
        }
        break;
    default:
        $page = '404';
}

$page = $page.'.twig';

// Render template or return json?
if($render) {
    // Render Header
    echo $twig->render('header.twig', $data);
    // Render Contents
    echo $twig->render($page, $data);
    // Render Footer
    echo $twig->render('footer.twig', $data);
} else {
    // Set Headers and return data
    header('Content-type: application/json');
    echo json_encode($json);
}

exit();

