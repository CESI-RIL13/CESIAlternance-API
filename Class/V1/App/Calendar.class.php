<?php
namespace App;

use Library\DB;
use Library\Entity;
use Library\Token;

class Calendar extends Entity {

    public function load() {
        $this->result['log'] = 'Calendar.load -> user_id: ' . Token::getUserId();
        $qry = "SELECT `p`.`id`, CONCAT(`t`.`alias`, SUBSTRING(YEAR(`p`.`begin`), 3)) AS `name`, `p`.`id_planning`" .
                "\nFROM `promo` AS `p` " .
                "\nINNER JOIN `user_promo` AS `up` ON `up`.`id_promo` = `p`.`id`" .
                "\nINNER JOIN `training_establishment` AS `te` ON `te`.`id` = `p`.`id_training_establishment`" .
                "\nINNER JOIN `training` AS `t` ON `t`.`id` = `te`.`id_training`" .
                "\nWHERE `up`.`id_user` = '" . Token::getUserId() . "'";
        if (Token::getUserRole() == 'stagiaire') $qry .= " AND NOW() BETWEEN `p`.`begin` AND `p`.`end`";
        $this->result['qry'] = $qry;
        $rs = DB::query($qry);
        if ($rs->rowCount() > 0) {
            $calendars = array();
            while ($rw = $rs->fetch(\PDO::FETCH_ASSOC)) {
                $url = 'http://www.google.com/calendar/feeds/' . $rw['id_planning'] . '@group.calendar.google.com/public/full?alt=json';
                $content = file_get_contents($url);
                if (!empty($content)) {
                    $calendar = array();
                    $planning = json_decode($content);
                    if (Token::getUserRole() == 'Intervenant') {
                        $calendar['id'] = 'inter_' . Token::getUserId() . '_cal';
                        $calendar['name'] = 'CESI Alternance';
                    } else {
                        $calendar['id'] = $rw['id_planning'];
                        $calendar['name'] = $planning->feed->title->{'$t'};
                    }
                    $calendar['events'] = array();
                    foreach ($planning->feed->entry as $entry) {
                        $event = array();
                        $event['id'] = trim(strrchr($entry->id->{'$t'}, '/'), '/');
                        $event['title'] = $entry->title->{'$t'};
                        //$event['subtitle'] = $entry->subtitle->{'$t'};
                        $event['content'] = $entry->content->{'$t'};
                        $dates = $entry->{'gd$when'}[0];
                        $event['startTime'] = $dates->startTime;
                        $event['endTime'] = $dates->endTime;
                        $event['where'] = $entry->{'gd$where'}[0]->valueString;
                        $event['updated'] = $entry->updated->{'$t'};
                        if ($this->isEnabledForUser($event['content'])) {
                            $calendar['events'][] = $event;
                        }
                    }
                    $calendars[] = $calendar;
                }
            }
            $this->result['result'] = $calendars;
        } else {
            $this->result['error'] = 'No Promo Calendar Found';
        }
    }

    private function isEnabledForUser($content) {
        if (Token::getUserRole() == 'Intervenant') {
            if (preg_match('@\(#(\d+)\)@', $content, $matches) == 1 && $matches[1] == Token::getUserId()) return true;
            return false;
        }
        return true;
    }

} 