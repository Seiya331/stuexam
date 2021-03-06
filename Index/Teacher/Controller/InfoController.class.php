<?php
namespace Teacher\Controller;

use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ChooseServiceModel;
use Teacher\Model\ExamServiceModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\FillServiceModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\JudgeServiceModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\ProblemServiceModel;
use Teacher\Model\ExamBaseModel;
use Think\Controller;

class InfoController extends TemplateController
{

    public function _initialize() {
        parent::_initialize();
    }

    public function showpaper() {
        if (isset($_GET['eid']) && isset($_GET['users'])) {
            $eid = intval(trim($_GET['eid']));
            $this->isCanWatchInfo($eid);

            $users = trim($_GET['users']);
            $row = ExamBaseModel::instance()->getExamInfoById($eid, array('title'));

            $_res = PrivilegeBaseModel::instance()->getPrivilegeByUserIdAndExamId($users, $eid);
            if (empty($_res)) {
                $this->echoError("The student have no privilege to take part in it");
            }

            $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($eid);

            $choosearr = ExamServiceModel::instance()->getUserAnswer($eid, $users, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            $judgearr = ExamServiceModel::instance()->getUserAnswer($eid, $users, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            $fillarr = ExamServiceModel::instance()->getUserAnswer($eid, $users, FillBaseModel::FILL_PROBLEM_TYPE);

            $chooseans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            $judgeans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            $fillans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($eid, FillBaseModel::FILL_PROBLEM_TYPE);
            $fillans2 = array();

            if ($fillans) {
                foreach ($fillans as $key => $value) {
                    $fillans2[$value['fill_id']] = ProblemServiceModel::instance()
                        ->getProblemsAndAnswer4Exam($value['fill_id'], ProblemServiceModel::PROBLEMANS_TYPE_FILL);
                }
            }
            $this->zadd('title', $row['title']);
            $this->zadd('allscore', $allscore);
            $this->zadd('choosearr', $choosearr);
            $this->zadd('judgearr', $judgearr);
            $this->zadd('fillarr', $fillarr);
            $this->zadd('chooseans', $chooseans);
            $this->zadd('judgeans', $judgeans);
            $this->zadd('fillans', $fillans);
            $this->zadd('fillans2', $fillans2);

            $this->auto_display('paper');
        } else {
            $this->echoError('Wrong Path');
        }
    }

    public function delscore() {
        if (isset($_GET['eid']) && isset($_GET['users'])) {
            $eid = intval(trim($_GET['eid']));
            $sortanum = I('get.sortanum', 0, 'intval');
            $sortdnum = I('get.sortdnum', 0, 'intval');
            $users = trim($_GET['users']);
            if (!$this->isOwner4ExamByExamId($eid)) {
                $this->echoError('You have no privilege to do it!');
            } else {
                M('ex_student')
                    ->where("exam_id=%d and user_id='%s'", $eid, $users)
                    ->delete();
                $this->redirect("Exam/userscore", array(
                    'eid' => $eid,
                    'sortdnum' => $sortdnum,
                    'sortanum' => $sortanum
                ));
            }
        } else {
            $this->echoError('Wrong Path');
        }
    }

    public function submitAllPaper() {
        $eid = I('get.eid', 0, 'intval');
        if (!empty($eid)) {

            $sortanum = I('get.sortanum', 0, 'intval');
            $sortdnum = I('get.sortdnum', 0, 'intval');
            if (!$this->isOwner4ExamByExamId($eid)) {
                $this->echoError('You have no privilege to do it!');
            }

            $allTakeIn = PrivilegeBaseModel::instance()->getTakeInExamUsersByExamId($eid);

            $allHaveScore = M('ex_student')->distinct('user_id')->field('user_id,score')
                ->where('exam_id=%d', $eid)->select();


            $haveScoreUserIds = array();
            $userIds2Submit = array();
            $negScoreUserId = array();

            foreach ($allHaveScore as $uid) {
                if ($uid['score'] >= 0) {
                    $haveScoreUserIds[] = strtolower($uid['user_id']);
                } else {
                    $negScoreUserId[strtolower($uid['user_id'])] = 1;
                }
            }

            foreach ($allTakeIn as $userId) {
                $_userId = strtolower($userId['user_id']);
                if (!in_array($_userId, $haveScoreUserIds)) {
                    $userIds2Submit[] = $_userId;
                }
            }

            if (!empty($userIds2Submit)) {
                $userIds2Submit = array_unique($userIds2Submit);
                $field = array('start_time', 'end_time');
                $prirow = ExamBaseModel::instance()->getExamInfoById($eid, $field);
                $start_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['start_time']));
                $end_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['end_time']));

                foreach ($userIds2Submit as $_uid) {
                    $mark = isset($negScoreUserId[$_uid]) ? 1 : 0;
                    $this->rejudgepaper($_uid, $eid, $start_timeC, $end_timeC, $mark);
                    usleep(10000);
                }
            }
            $this->redirect("Exam/userscore", array(
                'eid' => $eid,
                'sortdnum' => $sortdnum,
                'sortanum' => $sortanum
            ));
        } else {
            $this->alertError('Invaild Exam');
        }
    }

    public function DelAllUserScore() {
        $eid = I('post.eid', 0, 'intval');
        if (!empty($eid)) {
            if (!$this->isOwner4ExamByExamId($eid)) {
                $this->echoError('You have no privilege to do it!');
            }
            unset($_POST['eid']);
            $userIds = array();
            foreach ($_POST as $k => $v) {
                $userIds[] = mb_substr($k, 5);
            }
            if (!empty($userIds)) {
                $where = array(
                    'exam_id' => $eid,
                    'user_id' => array('in', $userIds)
                );
                M('ex_student')
                    ->where($where)
                    ->delete();
            }
            $this->redirect("Exam/userscore", array('eid' => $eid));
        }
    }

    public function submitpaper() {
        if (isset($_GET['eid']) && isset($_GET['users'])) {
            $eid = intval(trim($_GET['eid']));
            $userId = trim($_GET['users']);
            $sortanum = I('get.sortanum', 0, 'intval');
            $sortdnum = I('get.sortdnum', 0, 'intval');
            if (!$this->isOwner4ExamByExamId($eid)) {
                $this->echoError('You have no privilege to do it!');
            }
            $flag = $this->dojudgeone($eid, $userId);
            if ($flag) {
                $this->redirect("Exam/userscore", array(
                    'eid' => $eid,
                    'sortdnum' => $sortdnum,
                    'sortanum' => $sortanum
                ));
            }
        } else {
            $this->echoError('Wrong Path');
        }
    }

    public function hardSubmit() {
        $eid = I('get.eid', 0, 'intval');
        $userId = I('get.userId', '');
        $sortanum = I('get.sortanum', 0, 'intval');
        $sortdnum = I('get.sortdnum', 0, 'intval');
        if (!$this->isOwner4ExamByExamId($eid)) {
            $this->echoError('You have no privilege to do it!');
        }
        if (empty($eid) || empty($userId)) {
        } else {
            $this->dojudgeone($eid, $userId);
        }
        $this->redirect("Exam/userscore", array(
            'eid' => $eid,
            'sortdnum' => $sortdnum,
            'sortanum' => $sortanum
        ));
    }

    public function dorejudge() {
        if (IS_POST && I('post.eid')) {
            if (!check_post_key() || !$this->isSuperAdmin()) {
                $this->echoError('发生错误！');
            }
            $eid = intval($_POST['eid']);

            if (I('post.rjall')) {
                $prirow = ExamBaseModel::instance()->getExamInfoById($eid, array('start_time', 'end_time'));
                $start_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['start_time']));
                $end_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['end_time']));
                $userlist = M('ex_student')->field('user_id')->where('exam_id=%d', $eid)->select();
                if ($userlist) {
                    foreach ($userlist as $value) {
                        $this->rejudgepaper($value['user_id'], $eid, $start_timeC, $end_timeC, 1);
                    }
                    unset($userlist);
                }
                $this->success('全部重判成功！', U('Teacher/Exam/userscore', array('eid' => $eid)), 2);
            } else if (I('post.rjone')) {
                $rjuserid = test_input($_POST['rjuserid']);
                $flag = $this->dojudgeone($eid, $rjuserid);
                if ($flag) {
                    $this->success('重判成功！', U('Teacher/Exam/userscore', array('eid' => $eid)), 2);
                }
            } else
                $this->echoError('Invaild Path');
        } else {
            $this->echoError('Wrong Method');
        }
    }

    private function dojudgeone($eid, $userId) {
        $field = array('start_time', 'end_time');
        $prirow = ExamBaseModel::instance()->getExamInfoById($eid, $field);
        $start_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['start_time']));
        $end_timeC = strftime("%Y-%m-%d %X", strtotime($prirow['end_time']));

        $rightstr = "e$eid";
        $cnt1 = M('ex_privilege')
            ->where("user_id='%s' and rightstr='%s'", $userId, $rightstr)
            ->count();
        if ($cnt1 == 0) {
            $this->echoError('Student ID is Wrong!');
        } else {
            if (time() < $start_timeC) {
                $this->echoError('Exam Not Start');
            }
            $mark = M('ex_student')
                ->where("exam_id=%d and user_id='%s'", $eid, $userId)
                ->count();
            $this->rejudgepaper($userId, $eid, $start_timeC, $end_timeC, $mark);
            return true;
        }
        return false;
    }

    private function rejudgepaper($userId, $eid, $start_timeC, $end_timeC, $mark) {

        $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($eid);

        $choosesum = ChooseServiceModel::instance()->doRejudgeChooseByExamIdAndUserId($eid, $userId, $allscore['choosescore']);
        $judgesum = JudgeServiceModel::instance()->doRejudgeJudgeByExamIdAndUserId($eid, $userId, $allscore['judgescore']);
        $fillsum = FillServiceModel::instance()->doRejudgeFillByExamIdAndUserId($eid, $userId, $allscore);
        $programsum = ProblemServiceModel::instance()->doRejudgeProgramByExamIdAndUserId($eid, $userId, $allscore['programscore'], $start_timeC, $end_timeC);

        $sum = $choosesum + $judgesum + $fillsum + $programsum;
        if ($mark == 0) { // if the student has not submitted the paper
            $sql = "INSERT INTO `ex_student` VALUES('" . $userId . "','$eid','$sum','$choosesum','$judgesum','$fillsum','$programsum')";
            M()->execute($sql);
        } else {
            $sql = "UPDATE `ex_student` SET `score`='$sum',`choosesum`='$choosesum',`judgesum`='$judgesum',`fillsum`='$fillsum',`programsum`='$programsum'
			WHERE `user_id`='" . $userId . "' AND `exam_id`='$eid'";
            M()->execute($sql);
        }
    }
}

