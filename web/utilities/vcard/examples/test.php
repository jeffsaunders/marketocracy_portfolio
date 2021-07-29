<?php
// report all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// include the class file
require_once '../Contact/Vcard/Build.php';

// instantiate a builder object
// (defaults to version 3.0)
$vcard = new Contact_Vcard_Build();

// set a formatted name
$vcard->setFormattedName('Brandon McCarthy');

// set the structured name parts
$vcard->setName('McCarthy', 'Brandon', 'Christopher',
    'Mr.', 'II');

// add a work email.  note that we add the value
// first and the param after -- Contact_Vcard_Build
// is smart enough to add the param in the correct
// place.
$vcard->addEmail('mccarthyhe@gmail.com.com');
$vcard->addParam('TYPE', 'WORK');

// add a home/preferred email
$vcard->addEmail('mccarthyhe@gmail.com.com');
$vcard->addParam('TYPE', 'HOME');
$vcard->addParam('TYPE', 'PREF');

// add a work address
$vcard->addAddress('POB 101', 'Suite 202', '123 Main',
    'Beverly Hills', 'CA', '90210', 'US');
$vcard->addParam('TYPE', 'WORK');

// set the title (checks for colon-escaping)
$vcard->setTitle('The Title: The Subtitle');

// send the vcard
header('Content-Type: text/plain');
echo $vcard->fetch();
?>