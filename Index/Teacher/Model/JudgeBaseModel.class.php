<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:00
 */

namespace Teacher\Model;


class JudgeBaseModel extends GeneralModel
{

    const JUDGE_PROBLEM_TYPE = 2;

    private static $_instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    protected function getDao() {
        return M($this->getTableName());
    }

    protected function getTableName() {
        return DbConfigModel::TABLE_JUDGE;
    }

    protected function getTableFields() {
        return DbConfigModel::$TABLE_JUDGE_FILEDS;
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function getJudgeById($judgeId, $field = array()) {
        $dao = $this->getDao();
        $where = array(
            'judge_id' => $judgeId
        );
        $res = $dao->field($field)->where($where)->find();
        return $res;
    }

    public function updateJudgeById($judgeId, $data) {
        $dao = $this->getDao();
        $where = array(
            'judge_id' => $judgeId
        );
        $res = $dao->where($where)->data($data)->save();
        return $res;
    }

    public function insertJudgeInfo($data) {
        $dao = $this->getDao();
        $lastId = $dao->add($data);
        return $lastId;
    }

    public function delJudgeById($judgeId) {
        $dao = $this->getDao();
        $where = array(
            'judge_id' => $judgeId
        );
        $res = $dao->where($where)->delete();
        return $res;
    }

    /**
     * 获取某场考试下的判断题的题目和答案
     * @param $eid
     * @return mixed
     */
    public function getJudgeProblems4Exam($eid) {
        $type = self::JUDGE_PROBLEM_TYPE;
        $sql = "SELECT `ex_judge`.`judge_id`,`question`,`answer` FROM `ex_judge`,`exp_question`
		WHERE `exam_id`='$eid' AND `type`='$type' AND `ex_judge`.`judge_id`=`exp_question`.`question_id` ORDER BY `judge_id`";
        $ans = M()->query($sql);
        return $ans;
    }
}