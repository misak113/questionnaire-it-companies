<?php

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    /** @var \app\model\QuestionnaireModel @inject */
    public $questionnaireModel;

    public function handleStore() {
        $post = $this->getRawPost();
        if ($post === null) {
            $this->sendJson(array('error' => 'bad post data'));
        }
        $uid = $this->getUid();
        $this->questionnaireModel->store($uid, $post['questionnaire']);
        $this->sendJson($post);
    }

    public function handleSave() {
        $post = $this->getRawPost();
        if ($post === null) {
            $this->sendJson(array('error' => 'bad post data'));
        }
        $uid = $this->getUid();
        $this->questionnaireModel->save($uid, $post['questionnaire']);
        $this->sendJson($post);
    }

    public function handleWhisperCompanies() {
        $post = $this->getRawPost();
        $model = isset($post['model']) ?$post['model'] :'';
        $companies = $this->questionnaireModel->getCompaniesByName($model);
        $this->sendJson(array(
            'whisperer' => $companies
        ));
    }

    protected function getUid() {
        $section = $this->session->getSection('questionnaire');
        if (!isset($section['uid'])) {
            $section['uid'] = \Nette\Utils\Strings::random(24);
        }
        return $section['uid'];
    }

    protected function getRawPost() {
        if (!isset($GLOBALS['HTTP_RAW_POST_DATA']))
            return null;
        $raw = $GLOBALS['HTTP_RAW_POST_DATA'];
        try {
            $post = \Nette\Utils\Json::decode($raw, true);
        } catch (\Nette\Utils\JsonException $e) {
            return null;
        }
        return $post;
    }
}
