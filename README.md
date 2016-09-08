# Silverstripe Email Manager

A simple module that allows importing of and management of emails via
model admin

The intention of this module is to allow reciept and processing of emails
so that they can be added to larger applications (such as support desks or
CRM).

## Requirements

- [php-mime-mail-parser](https://github.com/php-mime-mail-parser/php-mime-mail-parser)
- [PHP Mimeparse extension](http://php.net/manual/en/book.mailparse.php)

## Installation (via composer)

```sh
$ composer require i-lateral/silverstripe-email-manager
```

## Importing emails

Emails can be imported directly (by setting up the server to recieve emails
and forward them to this module) or via IMAP.

### Forwarding

In order for this module to recieve email, it needs to be sent to it. There is
a Silverstripe task (EmailImportTask) that is designed to handle the job of
reading an email and importing it into the database

In order to import you need to set your mail server (eg Postfix) up to import
the email (there is an example of doing this at [TheCodingMachine](https://www.thecodingmachine.com/triggering-a-php-script-when-your-postfix-server-receives-a-mail/).

Your mail import filter will need to pipe the email to Silverstripe, you can do
this by stipulating a directory then the command path, EG:

```sh
pipe flags=X user=www-data directory=/path/to/ss/install argv=php ./framework/cli-script.php dev/tasks/EmailImportTask 
```

### Importing VIA IMAP

This feature is comming soon (hopefully)!