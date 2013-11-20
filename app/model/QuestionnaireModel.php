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
        $this->log($uid, $post);
        $questionnaire = $this->insertOrUpdate($uid, $data);
        return $questionnaire;
    }

    public function save($uid, $post) {
        $data = $this->prepareData($post);
        $data['saved'] = new DateTime();
        $this->log($uid, $post);
        $questionnaire = $this->insertOrUpdate($uid, $data);
        return $questionnaire;
    }

    protected function prepareData($post) {
        return $post;
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
        $questionnaire = $this->table()->where('uid = ?', $uid)->fetch();
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