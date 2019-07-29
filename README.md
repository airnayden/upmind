# upmind
Proof of Concept with 2FA and PHP

Just a simple app, written according to the requirements for the job application.

1. SQLite in use.
2. Twig in use.
3. Doctrine in use.
4. RobThree/TwoFactorAuth in use.

Demo: http://dev.aircode-bg.net/upmind/

How it works?

1. The user inputs username(email) and label 
2. The system makes a check if this user already exists => No => Inserts user + label + secret; Yes => updates label (if different) and gets the secret
3. Generating QR code based on the secret
4. Displaying a new form with username(email) and code fields.
5. On submit it checks for a valid user record, gets the secret and compares the code.
6. Returns Welcome or Error message.

PS: Edit .htaccess in order to set RewriteBase according to your configuration.

PS2: The inial idea was to build it as an extension for the OpenCart platform, but then I decided to create it from scratch.
