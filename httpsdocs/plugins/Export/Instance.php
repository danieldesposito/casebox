<?php
namespace Export;

use CB\L;
use CB\Util;
use CB\User;

class Instance
{
    public $columns = array();

    public function install()
    {

    }

    public function init()
    {
    }

    protected function getData($p)
    {
        $rez = array();
        if (empty($p)) {
            return $rez;
        }

        // form columns
        L\initTranslations();
        $this->columns = array(
            'nid' => 'ID'
            ,'name' => L\get('Name')
            ,'path' => L\get('Path')
            ,'date' => L\get('Date')
            ,'date_end' => 'End date'
            ,'oid' => L\get('Owner')
            ,'cid' => L\get('CreatedBy')
            ,'cdate' => L\get('Created')
            ,'uid' => L\get('heUpdated')
            ,'udate' => L\get('UpdatedDate')
        );

        // retreive data
        $p['start'] = 0;
        $p['rows'] = 500;

        $sr = new \CB\Browser();
        $results = $sr->getChildren($p);
        if (!empty($results['DC'])) {
            foreach ($results['DC'] as $colName => $col) {
                $this->columns[$colName] = @$col['title'];
            }
        }
        //insert header
        $rez[] = array_values($this->columns);

        while (!empty($results['data'])) {
            foreach ($results['data'] as $r) {
                $record = array();
                foreach ($this->columns as $colName => $colTitle) {
                    if (strpos($colName, 'date') === false) {
                        if (in_array($colName, array('cid', 'uid'))) {
                            $record[] = User::getDisplayName($r[$colName]);
                        } else {
                            $record[] =  @$r[$colName];
                        }
                    } else {
                        $value = Util\dateISOToMysql(@$r[$colName]);
                        $value = Util\formatMysqlTime($value);
                        $tmp = explode(' ', $value);
                        if (!empty($tmp[1]) && ($tmp[1] == '00:00')) {
                            $value = $tmp[0];
                        }
                        $record[] =  $value;
                    }

                }
                $rez[] = $record;
            }

            if (($p['start'] + $p['rows']) < $results['total']) {
                $p['start'] += $p['rows'];
                $results = $sr->getChildren($p);
            } else {
                $results['data'] = array();
            }
        }

        return $rez;
    }

    /**
     * get csv file
     *
     * @param $p object
     */
    public function getCSV($p)
    {
        $rez = array();
        $records = $this->getData($p);

        $rez[] = implode(';', array_shift($records));

        foreach ($records as &$r) {
            $record = array();
            foreach ($r as $t) {
                $t = strip_tags($t);

                if (!empty($t) && !is_numeric($t)) {
                    $t = str_replace(
                        array(
                            '"'
                            ,"\n"
                            ,"\r"
                        ),
                        array(
                            '""'
                            ,'\n'
                            ,'\r'
                        ),
                        $t
                    );
                    $t = '"'.$t.'"';
                }
                $record[] = $t;
            }

            $rez[] = implode(';', $record);
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Exported_Results_'.date('Y-m-d_Hi').'.csv');
        header("Pragma: no-cache");
        header("Expires: 0");
        echo implode("\n", $rez);
    }

    public function getHTML($p)
    {
        $rez = array();
        $records = $this->getData($p);

        $rez[] = '<th>'.implode('</th><th>', array_shift($records)).'</th>';

        foreach ($records as $r) {
            $record = array();
            foreach ($r as $t) {
                $t = strip_tags($t);
                $record[] = $t;
            }
            $rez[] = '<td>'.implode('</td><td>', $record).'</td>';
        }

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename=Exported_Results_'.date('Y-m-d_Hi').'.html');
        header("Pragma: no-cache");
        header("Expires: 0");
        echo '<!DOCTYPE html>
            <html>
            <header>
                <meta http-equiv="content-type" content="text/html; charset=utf-8" >
            </header>
            <body>
            <table border="1" style="border-collapse: collapse">
            <tr>';
        echo implode("</tr>\n<tr>", $rez);
        echo '</tr></table></body></html>';
    }
}
