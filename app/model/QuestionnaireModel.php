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
        $questionnaire = $this->insertOrUpdate($uid, $data);
        $this->log($uid, $post);
        return $questionnaire;
    }

    public function save($uid, $post) {
        $data = $this->prepareData($post);
        $data['saved'] = new DateTime();
        $questionnaire = $this->insertOrUpdate($uid, $data);
        $this->log($uid, $post);
        return $questionnaire;
    }

    protected function prepareData($post) {
        $data = array(
            'sector' => @$post['sector'],
            'xname' => @$post['xname'],
            'work_intensity' => @$post['work_intensity'],
            'work_duration' => @$post['work_duration'],
            'work_position' => array(
                'name' => @$post['work_position'],
            ),
            'company' => array(
                'name' => @$post['name'],
                'ic' => @$post['ic'],
                'size' => @$post['size'],
                'address_street' => @$post['address_street'],
                'address_city' => @$post['address_city'],
                'address_postcode' => @$post['address_postcode'],
            ),
            'manager_person' => array(
                'firstname' => @$post['manager_firtname'],
                'lastname' => @$post['manager_lastname'],
                'academy_title' => @$post['manager_academy_title'],
                'phone' => @$post['manager_phone'],
                'position' => array(
                    'name' => @$post['manager_position'],
                ),
            ),
            'developer_person' => array(
                'firstname' => @$post['developer_firtname'],
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

    protected function insertOrUpdate($uid, $data) {
        $questionnaire = $this->table()
            ->where('uid = ?', $uid)
            ->where('saved IS NULL')
            ->fetch();
        if (!$questionnaire) {
            $data['uid'] = $uid;
            try {
                $questionnaire = $this->table()
                    ->insert($data);
            } catch (\PDOException $e) {
                Debugger::log($e, Debugger::ERROR);
                return null;
            }
        } else {
            try {
                $questionnaire = $this->table()
                    ->where('uid = ?', $uid)
                    ->update($data);
            } catch (\PDOException $e) {
                Debugger::log($e, Debugger::ERROR);
                return null;
            }
        }
        return $questionnaire;
    }

    protected function table() {
        return $this->selectionFactory->table('questionnaire');
    }
    protected function tableLog() {
        return $this->selectionFactory->table('questionnaire_log');
    }
} 