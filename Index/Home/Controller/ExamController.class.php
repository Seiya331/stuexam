<?php
namespace Home\Controller;

use Home\Model\AnswerModel;
use Home\Model\ExamadminModel;
use Teacher\Model\ChooseBaseModel;
use Teacher\Model\ExamServiceModel;
use Teacher\Model\FillBaseModel;
use Teacher\Model\JudgeBaseModel;
use Teacher\Model\PrivilegeBaseModel;
use Teacher\Model\ProblemServiceModel;

class ExamController extends TemplateController
{
    private static $isruning = null;
    private $row;

    public function _initialize() {
        parent::_initialize();
        if (I('eid')) {
            $eid = I('eid', 0, 'intval');
            $user_id = $this->userInfo['user_id'];
            $this->row = ExamadminModel::instance()->chkexamprivilege($eid, $user_id, true);
            if (is_array($this->row)) {
                self::$isruning = ExamadminModel::instance()->chkruning($this->row['start_time'], $this->row['end_time']);
            }
        }
    }

    public function showquestion() {
        if (I('get.eid')) {
            $eid = I('get.eid', '', 'intval');
            $user_id = $this->userInfo['user_id'];
            $row = $this->row;
            if (!is_array($row)) {
                if ($row == 0) {
                    $this->echoError('You have no privilege!');
                } else if ($row == -1) {
                    $this->echoError('No Such Exam');
                } else if ($row == -2) {
                    $this->echoError('Do not login in diff machine,Please Contact administrator');
                } else if ($row == -3) {
                    $this->echoError('You have taken part in it');
                }
            }
            $rnd = PrivilegeBaseModel::instance()->getPrivilegeByUserIdAndExamId($user_id, $eid, array('randnum'));
            if ($this->isCreator()) {
                $rnd['randnum'] = 0;
            }
            $isruning = self::$isruning;
            if ($isruning != 1) {
                $this->redirect('Home/Index/index', '', 3, "<h2>Exam is not running</h2>");
            }
            $lefttime = strtotime($row['end_time']) - time();
            $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($eid);

            $choosearr = ExamServiceModel::instance()->getUserAnswer($eid, $user_id, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            $judgearr = ExamServiceModel::instance()->getUserAnswer($eid, $user_id, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            $fillarr = ExamServiceModel::instance()->getUserAnswer($eid, $user_id, FillBaseModel::FILL_PROBLEM_TYPE);

            $chooseans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);
            $judgeans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE);
            $fillans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($eid, FillBaseModel::FILL_PROBLEM_TYPE);
            $programans = ProblemServiceModel::instance()->getProblemsAndAnswer4Exam($eid, ProblemServiceModel::EXAMPROBLEM_TYPE_PROGRAM);

            $choosesx = ExamadminModel::instance()->getproblemsx($eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE, $rnd['randnum']);
            $judgesx = ExamadminModel::instance()->getproblemsx($eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE, $rnd['randnum']);
            $fillsx = ExamadminModel::instance()->getproblemsx($eid, FillBaseModel::FILL_PROBLEM_TYPE, $rnd['randnum']);

            $data = array(
                'extrainfo' => $lefttime + 1
            );
            PrivilegeBaseModel::instance()->updatePrivilegeByUserIdAndExamId($user_id, $eid, $data);

            $this->zadd('row', $row);
            $this->zadd('lefttime', $lefttime);
            $this->zadd('randnum', $rnd['randnum']);
            $this->zadd('allscore', $allscore);
            $this->zadd('choosearr', $choosearr);
            $this->zadd('judgearr', $judgearr);
            $this->zadd('fillarr', $fillarr);
            $this->zadd('choosesx', $choosesx);
            $this->zadd('judgesx', $judgesx);
            $this->zadd('fillsx', $fillsx);
            $this->zadd('chooseans', $chooseans);
            $this->zadd('judgeans', $judgeans);
            $this->zadd('fillans', $fillans);
            $this->zadd('programans', $programans);
            $this->auto_display(null, false);
        } else {
            $this->echoError('No Such Exam');
        }
    }

    public function saveanswer() {
        if (I('post.eid')) {
            $eid = intval($_POST['eid']);
            $user_id = $this->userInfo['user_id'];
            if (self::$isruning == 1) {
                AnswerModel::instance()->answersave($user_id, $eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE);//choose over
                AnswerModel::instance()->answersave($user_id, $eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE);//judge over
                AnswerModel::instance()->answersave($user_id, $eid, FillBaseModel::FILL_PROBLEM_TYPE);//fillover
                usleep(30000);
                echo "ok";
            } else {
                echo "保存失败!";
            }
        } else {
            echo "保存失败啦！";
        }
    }

    public function submitpaper() {
        if (I('post.eid')) {
            $eid = intval($_POST['eid']);
            $user_id = $this->userInfo['user_id'];
            $row = $this->row;
            if (!is_array($row)) {
                if ($row == 0) $this->echoError('You have no privilege!');
                else if ($row == -1) {
                    $this->echoError('No Such Exam');
                } else if ($row == -2) {
                    $this->echoError('Do not login in diff machine,Please Contact administrator');
                } else if ($row == -3) {
                    $this->echoError('You have taken part in it');
                }
            } else {
                if (self::$isruning != 1) {
                    $this->redirect('Home/Index/index', '', 3, '考试不在进行!');
                } else {
                    $start_timeC = strftime("%Y-%m-%d %X", strtotime($row['start_time']));
                    $end_timeC = strftime("%Y-%m-%d %X", strtotime($row['end_time']));
                    $allscore = ExamServiceModel::instance()->getBaseScoreByExamId($eid);
                    $cright = AnswerModel::instance()->answersave($user_id, $eid, ChooseBaseModel::CHOOSE_PROBLEM_TYPE, false);
                    $jright = AnswerModel::instance()->answersave($user_id, $eid, JudgeBaseModel::JUDGE_PROBLEM_TYPE, false);
                    $fscore = AnswerModel::instance()->answersave($user_id, $eid, FillBaseModel::FILL_PROBLEM_TYPE, false);
                    $pright = AnswerModel::instance()->getrightprogram($user_id, $eid, $start_timeC, $end_timeC);
                    $inarr['user_id'] = $user_id;
                    $inarr['exam_id'] = $eid;
                    $inarr['choosesum'] = $cright * $allscore['choosescore'];
                    $inarr['judgesum'] = $jright * $allscore['judgescore'];
                    $inarr['fillsum'] = $fscore;
                    $inarr['programsum'] = $pright * $allscore['programscore'];
                    $inarr['score'] = $inarr['choosesum'] + $inarr['judgesum'] + $inarr['fillsum'] + $inarr['programsum'];
                    M('ex_student')->add($inarr);
                    $this->success('试卷已成功提交！', U("Home/Index/score"), 3);
                }
            }
        } else {
            $this->redirect('/Home', '', 3, 'Wrong Method');
        }
    }

    public function prgsubmit() {
        if (I('post.eid')) {
            $eid = intval($_POST['eid']);
            $id = intval($_POST['id']);
            $user_id = $this->userInfo['user_id'];
            $language = intval($_POST['language']);
            if ($language > 9 || $language < 0) $language = 0;
            $language = strval($language);

            if (self::$isruning == 1) {
                $source = $_POST['source'];
                if (get_magic_quotes_gpc()) {
                    $source = stripslashes($source);
                }
                $source = addslashes($source);
                $len = strlen($source);
                $OJ_DATA = C('OJ_DATA');
                $OJ_APPENDCODE = C('OJ_APPENDCODE');
                $extarr = C('language_ext');
                $ext = $extarr[$language];
                $prefix_file = "$OJ_DATA/$id/prefix.$ext";
                $append_file = "$OJ_DATA/$id/append.$ext";
                if ($OJ_APPENDCODE && file_exists($prefix_file)) {
                    $source = addslashes(file_get_contents($prefix_file) . "\n") . $source;
                }
                if ($OJ_APPENDCODE && file_exists($append_file)) {
                    $source .= addslashes("\n" . file_get_contents($append_file));
                }

                $ip = $_SERVER['REMOTE_ADDR'];
                if ($len <= 2) {
                    echo "Source Code Too Short!";
                    exit(0);
                }
                if ($len > 65536) {
                    echo "Source Code Too Long!";
                    exit(0);
                }

                $sql = "SELECT `in_date` FROM `solution` WHERE `user_id`='" . $user_id . "' AND `in_date`>NOW()-10 ORDER BY `in_date` DESC LIMIT 1";
                $row = M()->query($sql);
                if ($row) {
                    echo "You should not submit more than twice in 10 seconds.....<br>";
                    exit(0);
                }
                $arr['problem_id'] = $id;
                $arr['user_id'] = $user_id;
                $arr['in_date'] = date('Y-m-d H:i:s');
                $arr['language'] = $language;
                $arr['ip'] = $ip;
                $arr['code_length'] = $len;
                $insert_id = M('solution')->add($arr);
                $sql = "INSERT INTO `source_code`(`solution_id`,`source`) VALUES('$insert_id','$source')";
                M()->execute($sql);
                $sql = "UPDATE `problem` SET `in_date`=NOW() WHERE `problem_id`=$id";
                M()->execute($sql);
                $colorarr = C('judge_color');
                $resultarr = C('judge_result');
                $color = $colorarr[0];
                $result = $resultarr[0];
                echo "<font color=$color size='5px'>$result</font>";
            } else {
                echo "考试已结束!";
            }
        } else {
            echo "提交失败3";
        }
    }

    public function updresult() {
        if (I('get.id')) {
            $eid = intval($_GET['eid']);
            $id = intval($_GET['id']);
            $user_id = $this->userInfo['user_id'];
            $row = $this->row;
            if (self::$isruning == 1) {
                $start_timeC = strftime("%Y-%m-%d %X", strtotime($row['start_time']));
                $end_timeC = strftime("%Y-%m-%d %X", strtotime($row['end_time']));

                $where = array(
                    'user_id' => $user_id,
                    'exam_id' => $eid,
                    'type'    => 4,
                    'question_id' => $id,
                    'answer_id' => 1,
                    'answer' => 4
                );
                $row_cnt = M('ex_stuanswer')->where($where)->find();
                    //->where("problem_id=%d and user_id='%s' and result=4 and in_date>'$start_timeC' and in_date<'$end_timeC'", $id, $user_id)
                if (!empty($row_cnt)) {
                    echo "<font color='blue' size='3px'>此题已正确,请不要重复提交</font>";
                } else {
                    $trow = M('solution')->field('result')
                        ->where("problem_id=%d and user_id='%s' and in_date>'$start_timeC' and in_date<'$end_timeC'", $id, $user_id)
                        ->order('solution_id desc')
                        ->find();
                    if (!$trow) {
                        echo "<font color='green' size='5px'>未提交</font>";
                    } else {
                        $ans = $trow['result'];
                        if ($ans == 4) {
                            ProblemServiceModel::instance()->syncProgramAnswer($user_id, $eid, $id, $ans);
                        }
                        $colorarr = C('judge_color');
                        $resultarr = C('judge_result');
                        $color = $colorarr[$ans];
                        $result = $resultarr[$ans];
                        echo "<font color=$color size='5px'>$result</font>";
                    }
                }
            } else {
                echo "考试已结束!";
            }
        } else {
            echo "提交失败3";
        }
    }

    public function programSave() {
        if (self::$isruning == 1) {
            $pid = I('post.pid', 0, 'intval');
            $eid = I('post.eid', 0, 'intval');
            $userId = $this->userInfo['user_id'];
            $start_timeC = strftime("%Y-%m-%d %X", strtotime($this->row['start_time']));
            $end_timeC = strftime("%Y-%m-%d %X", strtotime($this->row['end_time']));
            $row_cnt = M('solution')
                ->where("problem_id=%d and user_id='%s' and result=4 and in_date>'$start_timeC' and in_date<'$end_timeC'", $pid, $userId)
                ->count();
            if ($row_cnt) {
                ProblemServiceModel::instance()->syncProgramAnswer($userId, $eid, $pid, 4);
                echo 4;
            } else {
                echo $pid . '___' . $eid;
            }
        }
    }
}

