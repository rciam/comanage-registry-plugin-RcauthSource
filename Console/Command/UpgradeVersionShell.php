<?php
/*
 * For execution run: Console/cake RcauthSource.upgradeVersion <version>
 * */
class UpgradeVersionShell extends AppShell
{
    public $uses = array('RcauthSource');

    public function main() {      
        $targetVersion = null;
        if(!empty($this->args[0])) {
            // Use requested target version
            $targetVersion = $this->args[0];
            $fn = '_ug' . $targetVersion;
            if(method_exists($this, $fn)) {
                $this->$fn();
            }
            else {
                $this->out(_txt('er.ug.fail'));
                $this->out('This version does not exist.');
                exit;
            }
        }
        else {
            $this->out('Please provide target version');
        }      
    }

    public function _ug110()
    {
        $query = "alter table cm_rcauth_sources alter column client_secret type varchar(1024) using client_secret::varchar(1024);";
        $query .= "alter table cm_rcauth_sources add scopes varchar(256)";
        $dbc = $this->RcauthSource->getDataSource();
        $dbc->begin();
        try {
            $this->RcauthSource->query($query);
            $dbc->commit();
            $this->out('Change applied: ' . $query);
        }
        catch(Exception $e) {
            $dbc->rollback();
            $this->out($e->getMessage());
        }
    }
}
