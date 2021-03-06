<?php

namespace CB\Objects\Plugins;

use CB\Objects;
use CB\Util;

class ObjectProperties extends Base
{
    public function getData($id = false)
    {
        $rez = array(
            'success' => true
        );
        parent::getData($id);

        $preview = Objects::getPreview($this->id);
        $data = Objects::getCachedObject($this->id)->getData();

        if (!empty($preview)) {
            $rez['data'] = array(
                'html' => $preview
            );
        }

        if (!empty($data)) {
            if (!empty($data['pids'])) {
                $rez['data']['path'] = str_replace(',', '/', $data['pids']);
            }

            foreach ($data as $k => $v) {
                if (in_array(
                    $k,
                    array(
                        'id'
                        ,'name'
                        ,'template_id'
                        ,'date_end'
                        ,'cid'
                        ,'cdate'
                        ,'can'
                    )
                )) {
                    if (in_array($k, array('date', 'date_end', 'cdate'))) {
                        $v = Util\dateMysqlToISO($v);
                    }

                    $rez['data'][$k] = $v;
                }
            }
        }

        return $rez;
    }
}
