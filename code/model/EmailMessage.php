<?php

use \PhpMimeMailParser\Parser as PHPMimeParser;

class EmailMessage extends DataObject implements PermissionProvider
{
    
    private static $db = array(
        "To" => "Varchar(255)",
        "From" => "Varchar(255)",
        "Subject" => "Varchar(255)",
        "MessageID" => "Varchar(255)",
        "Source" => "Text"
    );

    private static $casting = array(
        "Title" => "Varchar",
        "Body" => "Text",
        "HTMLBody" => "HTMLText"
    );

    private static $summary_fields = array(
        "Subject",
        "To",
        "From"
    );

    /**
     * Return an array of email addresses from a provided string
     * 
     * @param $string A string that contains email addresses
     * @return array
     */
    public static function get_emails_from_string($string)
    {
        $return = array();
        $re = "/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,6})/i";
        preg_match_all($re, $string, $matches);

        // Clean up the results intyo a simpler array
        foreach ($matches as $match) {
            $return[] = $match[0];
        }

        return array_unique($return);
    }

    public function getTitle()
    {
        return $this->Subject . "<{$this->From}>";
    }

    /**
     * Run email parser over message source and parse,
     * then return the parsed object
     *
     * @return \PhpMimeMailParser\Parser
     */
    public function getParser()
    {
        $parser = new PHPMimeParser();
        $parser->setText($this->Source);
        return $parser;
    }

    /**
     * Get the raw body of the email
     *
     * @return string
     */
    public function getBody()
    {
        $parser = $this->getParser();
        $return = $parser->getMessageBody();
        return $return;
    }

    /**
     * Get the HTML body of the email
     *
     * @return string
     */
    public function getHTMLBody()
    {
        $parser = $this->getParser();
        $return = $parser->getMessageBody('html');
        return $return;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Move the source of the email to a seperate tab
        $fields->addFieldToTab(
            "Root.Source",
            TextareaField::create(
                "Source",
                ""
            )->addExtraClass("stacked")
            ->setRows(40)
        );

        return $fields;
    }

    /**
     * Create a new email message based on a provided string.
     * 
     * @param $source The email as a string to import
     * @return EmailMessage
     */
    public function import($source)
    {
        $parser = new PHPMimeParser();

        // 3. Specify the raw mime mail text.
        $parser->setText($source);

        // Add source code of the email to DB
        $this->Source = $source;
        $this->Subject = $parser->getHeader('subject');
        $this->To = $parser->getHeader('to');
        $this->From = $parser->getHeader('from');
        $this->MessageID = $parser->getHeader('message-id');

        $this->extend("onBeforeImport");
        
        return $this;
    }

    public function providePermissions()
    {
        return array(
            "MESSAGE_MANAGE" => array(
                'name' => 'Manage Messages',
                'help' => 'Allow user to edit, assign and delete messages',
                'category' => 'Messages',
                'sort' => 10
            ),
        );
    }

    /**
     * Only users with correct permissions can view
     *
     * @return Boolean
     */
    public function canView($member = false) {
        if($member instanceof Member) {
            $memberID = $member->ID;
        } elseif(is_numeric($member)) {
            $memberID = $member;
        } else {
            $memberID = Member::currentUserID();
        }

        if($memberID && Permission::checkMember($memberID, array("ADMIN", "MESSAGE_MANAGE"))) {
            return true;
        }

        return false;
    }

    /**
     * Messages cannot be created via the web admin interface
     *
     * @return Boolean
     */
    public function canCreate($member = null) {
        return false;
    }

    /**
     * Messages cannot be edited via the web admin interface
     * (they have been imported)
     *
     * @return Boolean
     */
    public function canEdit($member = null) {
        return false;
    }

    /**
     * Only users with correct permissions can delete
     *
     * @return Boolean
     */
    public function canDelete($member = null) {
        if($member instanceof Member) {
            $memberID = $member->ID;
        } elseif(is_numeric($member)) {
            $memberID = $member;
        } else {
            $memberID = Member::currentUserID();
        }

        if ($memberID && Permission::checkMember($memberID, array("ADMIN", "MESSAGE_MANAGE"))) {
            return true;
        }

        return false;
    }
}