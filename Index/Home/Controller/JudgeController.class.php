<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 19:42
 */

namespace Home\Controller;

use Home\Model\AnswerModel;
use Home\Model\ExamadminModel;
use Teacher\Model\ExamServiceModel;
use Teacher\Model\ProblemServiceModel;

class JudgeController extends QuestionController
{

    public function _initialize() {
        parent::_initialize();
    }

    public function index() {

        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($this->examId);
        $judgearr = ExamServiceModel::instance()->getUserAnswer($this->examId, $this->userInfo['user_id'], 2);
        $judgeans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($this->examId, 2);
        $judgesx = ExamadminModel::instance()->getproblemsx($this->examId, 2, $this->randnum);

        $this->zadd('allscore', $allscore);
        $this->zadd('judgearr', $judgearr);
        $this->zadd('judgesx', $judgesx);
        $this->zadd('judgeans', $judgeans);

        $this->auto_display('Exam:judge', false);
    }
}