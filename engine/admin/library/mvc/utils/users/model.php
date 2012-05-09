<?php

namespace admin\library\mvc\utils\users;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\grid as dhtmlxGrid;
// ORM
use ORM\users as usersOrm;
use ORM\users\group as usersGroupOrm;
use ORM\users\type as usersTypeOrm;

/**
 * @author Козленко В.Л.
 */
class model {

    CONST USER_COUNT = 5;
    
    public function getUserXmlGrid($posStart){
        $userOrm = new usersOrm();
        $userCount = $userOrm->get('count(id)', null);
        $data = array();
        $data['body'] = $userOrm->select('ul.id, 0, "", ul.login, ut.name type, ul.phone, ul.enable', 'ul')
                ->join(usersTypeOrm::TABLE . ' ut', 'ul.typeId = ut.id AND ut.sysname="simple"')
                ->order('ul.date_add DESC')
                ->limit($posStart . ', ' . self::USER_COUNT)
                ->comment(__METHOD__)
                ->fetchAll();
        return dhtmlxGrid::createXMLOfArray($data, null, null, $userCount, $posStart);
        // func. getUserXmlGrid
    }
    
    public function saveData($pData){
        $return = array();
        // Парсим данные
        $data = json_decode($pData, true);
        if ($data) {
            $usersOrm = new usersOrm();
            // Бегаем по данным
            foreach ($data as $item) {
                if (!isset($item['id'])) {
                    throw new \Exception('Неверный JSON', 234);
                }
                $userId = (int) $item['id'];
                $saveData = array(
                    'enable' => (int)$item['data']['enable']
                );

                $newId = $usersOrm->save('id=' . $userId, $saveData);
                $return[$userId] = $newId ? : $userId;
            }// foreach
        } // if
        return $return;
    }

// class model
}

?>