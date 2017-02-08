<?php

use \PhpMimeMailParser\Parser as PHPMimeParser;

class EmailMessage extends DataObject implements PermissionProvider
{
    
    private static $db = array(
        "To" => "Varchar(255)",
        "From" => "Varchar(255)",
        "Subject" => "Varchar(255)",
        "MessageID" => "Varchar(255)",
        "Sent" => "SS_DateTime",
        "Source" => "Text"
    );

    private static $casting = array(
        "Title" => "Varchar",
        "Body" => "Text",
        "BodySummary" => "Text",
        "HTMLBody" => "HTMLText"
    );

    private static $summary_fields = array(
        "Subject" => "Subject",
        "From" => "From",
        "BodySummary" => "Summary",
        "Sent" => "Sent"
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

        // Clean up the results into a simpler array
        foreach ($matches as $match) {
            if ($match && is_array($match)) {
                $return[] = $match[0];
            }
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
     * Get a short summary of the body text
     *
     * @return string
     */
    public function getBodySummary()
    {
        $return = new Text("BodySummary");
        $return->setValue($this->Body);
        return $return->Summary();
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

        // Add email body
        $body = new HTMLText("Body");
        if ($this->HTMLBody) {
            $body->setValue($this->HTMLBody);
        } else {
            $body->setValue(nl2br($this->Body));
        }

        // Manually inject HTML for totals as Silverstripe refuses to render HTML
        $field_html = '<div id="Body" class="field readonly">';
        $field_html .= '<label class="left" for="Form_ItemEditForm_Body">';
        $field_html .= _t("EmailManager.EmailBody", "Body");
        $field_html .= '</label>';
        $field_html .= '<div class="middleColumn"><span id="Form_ItemEditForm_Body" class="readonly">';
        $field_html .= $body;
        $field_html .= '</span></div></div>';

        $fields->addFieldToTab(
            "Root.Main",
            LiteralField::create("Body", $field_html),
            "MessageID"
        );

        // Move the source of the email to a seperate tab
        $source = new HTMLText("Source");
        $source->setValue(nl2br($this->Source));


        // Manually inject HTML for totals as Silverstripe refuses to render HTML
        $field_html = '<div id="Source" class="field stacked readonly">';
        $field_html .= '<div class="middleColumn"><span id="Form_ItemEditForm_Source" class="readonly">';
        $field_html .= $source;
        $field_html .= '</span></div></div>';
        
        $fields->addFieldToTab(
            "Root.Source",
            LiteralField::create("Source",$field_html)
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

        // Convert the date
        $date = DateTime::createFromFormat('D, d M Y H:i:s O', $parser->getHeader("date"));
        $this->Sent = $date->format('Y-m-d H:i:s');

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