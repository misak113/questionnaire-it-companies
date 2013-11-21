<?php
/**
 * Created by PhpStorm.
 * User: misak113
 * Date: 20.11.13
 * Time: 23:50
 */

namespace app\model;


use Nette\Database\SelectionFactory;
use Nette\DateTime;
use Nette\Diagnostics\Debugger;

class QuestionnaireModel {
    /** @var \Nette\Database\SelectionFactory @inject */
    public $selectionFactory;

    public function store($uid, $post) {
        $data = $this->prepareData($post);
        $questionnaire = $this->updateQuestionnaire($uid, $data);
        $this->log($uid, $post);
        $this->cleanup();
        return $questionnaire;
    }

    public function save($uid, $post) {
        $data = $this->prepareData($post);
        $data['saved'] = new DateTime();
        $questionnaire = $this->updateQuestionnaire($uid, $data);
        $this->log($uid, $post);
        $this->cleanup();
        return $questionnaire;
    }

    protected function prepareData($post) {
        $data = array(
            'sector' => @$post['sector']['value'],
            'xname' => @$post['xname'],
            'work_intensity' => @$post['work_intensity'],
            'work_duration' => @$post['work_duration']['value'],
            'work_position' => array(
                'name' => @$post['work_position'],
            ),
            'company' => array(
                'name' => @$post['company_name'],
                'ic' => @$post['company_ic'],
                'size' => @$post['company_size']['value'],
                'address_street' => @$post['company_address_street'],
                'address_city' => @$post['company_address_city'],
                'address_postcode' => @$post['company_address_postcode'],
            ),
            'manager' => array(
                'firstname' => @$post['manager_firstname'],
                'lastname' => @$post['manager_lastname'],
                'academy_title' => @$post['manager_academy_title'],
                'phone' => @$post['manager_phone'],
                'position' => array(
                    'name' => @$post['manager_position'],
                ),
            ),
            'developer' => array(
                'firstname' => @$post['developer_firstname'],
                'lastname' => @$post['developer_lastname'],
                'academy_title' => @$post['developer_academy_title'],
                'phone' => @$post['developer_phone'],
                'position' => array(
                    'name' => @$post['developer_position'],
                ),
            ),
            'saved' => null
        );
        return $data;
    }

    protected function prepareLogData($data) {
        $data = array(
            'sector' => @$data['sector']['label'],
            'company_name' => @$data['company_name'],
            'company_ic' => @$data['company_ic'],
            'company_size' => @$data['company_size']['label'],
            'company_address_street' => @$data['company_address_street'],
            'company_address_city' => @$data['company_address_city'],
            'company_address_postcode' => @$data['company_address_postcode'],
            'xname' => @$data['xname'],
            'work_intensity' => @$data['work_intensity'],
            'work_duration' => @$data['work_duration']['label'],
            'work_position' => @$data['work_position'],
            'manager_position' => @$data['manager_position'],
            'manager_firstname' => @$data['manager_firstname'],
            'manager_lastname' => @$data['manager_lastname'],
            'manager_academy_title' => @$data['manager_academy_title'],
            'manager_phone' => @$data['manager_phone'],
            'developer_position' => @$data['developer_position'],
            'developer_firstname' => @$data['developer_firstname'],
            'developer_lastname' => @$data['developer_lastname'],
            'developer_academy_title' => @$data['developer_academy_title'],
            'developer_phone' => @$data['developer_phone'],
        );

        return $data;
    }

    protected function updateQuestionnaire($uid, $data) {
        // work_position
        $work_position = $this->insertOrUpdate('position', $data['work_position'], $data['work_position']);
        unset($data['work_position']);
        $data['work_position_id'] = $work_position['position_id'];

        // manager
        $manager_position = $this->insertOrUpdate('position', $data['manager']['position'], $data['manager']['position']);
        unset($data['manager']['position']);
        $data['manager']['position_id'] = $manager_position['position_id'];
        $manager = $this->insertOrUpdate('person', $data['manager'], $data['manager']);
        unset($data['manager']);
        $data['manager_person_id'] = $manager['person_id'];

        // developer
        $developer_position = $this->insertOrUpdate('position', $data['developer']['position'], $data['developer']['position']);
        unset($data['developer']['position']);
        $data['developer']['position_id'] = $developer_position['position_id'];
        $developer = $this->insertOrUpdate('person', $data['developer'], $data['developer']);
        unset($data['developer']);
        $data['developer_person_id'] = $developer['person_id'];

        // company
        $company = $this->insertOrUpdate('company', $data['company'], $data['company']);
        unset($data['company']);
        $data['company_id'] = $company['company_id'];

        // questionnaire
        $questionnaire = $this->insertOrUpdate('questionnaire', array('uid' => $uid), $data);

        return $questionnaire;
    }

    protected function cleanup() {
        $this->selectionFactory->getConnection()->query('CALL cleanup();');
    }

    protected function log($uid, $post) {
        $data = $this->prepareLogData($post);
        $data['uid'] = $uid;
        $data['logged'] = new DateTime();
        try {
            $questionnaire = $this->tableLog()
                ->insert($data);
        } catch (\PDOException $e) {
            Debugger::log($e, Debugger::ERROR);
            return null;
        }

        return $questionnaire;
    }

    protected function insertOrUpdate($tableName, $keys, $data) {
        $table = $this->selectionFactory->table($tableName);
        $row = $table
            ->where($keys)
            ->fetch();

        $table = $this->selectionFactory->table($tableName);
        if (!$row) {
            $data = $keys + $data;
            try {
                $row = $table
                    ->insert($data);
            } catch (\PDOException $e) {
                Debugger::log($e, Debugger::ERROR);
                return null;
            }
        } else {
            try {
                $countRows = $table
                    ->where($keys)
                    ->update($data);
            } catch (\PDOException $e) {
                Debugger::log($e, Debugger::ERROR);
                return null;
            }
        }
        return $row;
    }

    protected function table() {
        return $this->selectionFactory->table('questionnaire');
    }
    protected function tableLog() {
        return $this->selectionFactory->table('questionnaire_log');
    }
} 