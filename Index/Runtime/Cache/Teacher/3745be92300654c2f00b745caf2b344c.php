<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="程序设计考试 山东工商学院">
	<meta name="keywords" content="Exam,SDIBT,山东工商学院,程序设计考试">
	<!-- yours css -->
	<link rel="stylesheet" type="text/css" href="/JudgeOnline/stuexam/Public/Css/examsys.min.css" />
	<!-- Bootstrap -->
	<link rel="stylesheet" type="text/css" href="/JudgeOnline/stuexam/Public/Css/bootstrap.min.css" />
	<!--[if lt IE 9]>
		<script src="/JudgeOnline/stuexam/Public/Js/html5shiv.min.js"></script>
		<script src="/JudgeOnline/stuexam/Public/Js/respond.min.js"></script>
	<![endif]-->
</head>
<body>
<div class="navbar navbar-fixed-top navbar-default exam_header" role="navigation">
  <div class="container">
  	<div class="navbar-header">
  	  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" 
  	  data-target="#header-navbar">
  	  	<span class="sr-only">header toggle</span>
  	  	<span class="icon-bar"></span>
  	  	<span class="icon-bar"></span>
  	  	<span class="icon-bar"></span>
  	  </button>
  	  <a href="#" class="navbar-brand exam_navbar-brand">程序设计考试后台管理</a>
  	</div> <!-- navbar-header-end -->
	<div class="collapse navbar-collapse" id="header-navbar">
	<ul class="nav navbar-nav">
	  <li id='navexam'><a href="<?php echo U('/Teacher');?>">考试管理</a></li>
	  <li id='navchoose'><a href="<?php echo U('Teacher/Index/choose');?>">选择题管理</a></li>
	  <li id='navjudge'><a href="<?php echo U('Teacher/Index/judge');?>">判断题管理</a></li>
	  <li id='navfill'><a href="<?php echo U('Teacher/Index/fill');?>">填空题管理</a></li>
	  <li id='navpoint'><a href="<?php echo U('Teacher/Index/point');?>">知识点管理</a></li>
	  <li><a href="<?php echo U('/Home');?>">退出管理页面</a></li>
	</ul> <!-- first ul end -->
	<ul class="nav navbar-nav navbar-right">
		<li><a href="javascript:;">欢迎您： <?php echo (session('user_id')); ?></a></li>
	</ul> <!-- the second ul end -->
   </div> <!-- collapse navbar-collapse end -->
  </div> <!-- container-fluid end -->
</div> <!-- navbar end -->

<script type="text/javascript" src="/JudgeOnline/stuexam/Public/Js/jquery-1.11.2.min.js"></script>
<script type="text/javascript" src="/JudgeOnline/stuexam/Public/Js/Teacher.min.js"></script>
<script>
	$(function(){
		var url = window.location.href;
		if(url.indexOf('fill')!=-1){
			$("#navfill").addClass('active');
		}
		else if(url.indexOf('choose')!=-1){
			$("#navchoose").addClass('active');
		}
		else if(url.indexOf('judge')!=-1){
			$("#navjudge").addClass('active');
		}
		else if(url.indexOf('point')!=-1){
			$("#navpoint").addClass('active');
		}
		else{
			$("#navexam").addClass('active');
		}
	});
</script>

<div class="container exam_content">
<h2>考试分析</h2>
<div style='padding-bottom:7px'>
<ul class="nav nav-pills">
<li id='exam_index'><a href="<?php echo U('Teacher/Exam/index',array('eid'=>$eid));?>">试卷一览</a></li>
<li id='exam_choose'><a href="<?php echo U('Teacher/Problem/add',array('eid'=>$eid,'type'=>1));?>">选择题</a></li>
<li id='exam_judge'><a href="<?php echo U('Teacher/Problem/add',array('eid'=>$eid,'type'=>2));?>">判断题</a></li>
<li id='exam_fill'><a href="<?php echo U('Teacher/Problem/add',array('eid'=>$eid,'type'=>3));?>">填空题</a></li>
<li id='exam_program'><a href="<?php echo U('Teacher/Problem/add',array('eid'=>$eid,'type'=>4));?>">程序题</a></li>
<li id='exam_adduser'><a href="<?php echo U('Teacher/Exam/adduser',array('eid'=>$eid));?>">添加考生</a></li>
<li id='exam_userscore'><a href="<?php echo U('Teacher/Exam/userscore',array('eid'=>$eid));?>">考生成绩</a></li>
<li id='exam_analysis'><a href="<?php echo U('Teacher/Exam/analysis',array('eid'=>$eid));?>">考试分析</a></li>
<li id='exam_rejudge'><a href="<?php echo U('Teacher/Exam/rejudge',array('eid'=>$eid));?>">REJUDGE</a></li>
</ul>
</div>

<form class='form-inline'>
<input type="hidden" name="eid" value="<?php echo ($eid); ?>">
<div class="form-group">
	<input type="text" class="form-control input-lg search-input" id="student" name='student' value="<?php echo ($student); ?>" placeholder="查询专业或学生关键词如jk">
</div>
<button type="submit" class="btn btn-default btn-lg">Search</button>
</form>
<table class="table-bordered table table-bordered">
	<tr><td colspan=2><strong>考试编号:<?php echo ($eid); ?></strong></td>
		<td colspan=2><strong>应考人数:<?php echo ($totalnum); ?></strong></td>
		<td colspan=2><strong>实考人数:<?php echo ($row['realnum']); ?></strong></td>
	</tr>
	<tr><td colspan=6><h3>各部分得分</h3></td></tr>
	<tr>
		<td>题型</td>
		<td>选择题</td>
		<td>判断题</td>
		<td>填空题</td>
		<td>程序设计题</td>
		<td>总分</td>
	</tr>
	<tr>
		<td>最高分</td>
		<td><?php echo ($row['choosemax']); ?></td>
		<td><?php echo ($row['judgemax']); ?></td>
		<td><?php echo ($row['fillmax']); ?></td>
		<td><?php echo ($row['programmax']); ?></td>
		<td><?php echo ($row['scoremax']); ?></td>
	</tr>
	<tr>
		<td>最低分</td>
		<td><?php echo ($row['choosemin']); ?></td>
		<td><?php echo ($row['judgemin']); ?></td>
		<td><?php echo ($row['fillmin']); ?></td>
		<td><?php echo ($row['programmin']); ?></td>
		<td><?php echo ($row['scoremin']); ?></td>
	</tr>
	<tr>
		<td>平均分</td>
		<td><?php echo ($row['chooseavg']); ?></td>
		<td><?php echo ($row['judgeavg']); ?></td>
		<td><?php echo ($row['fillavg']); ?></td>
		<td><?php echo ($row['programavg']); ?></td>
		<td><?php echo ($row['scoreavg']); ?></td>
	</tr>
</table>
<table class="table-bordered table table-bordered">
	<tr><td colspan=6><h3>各分数段人数</h3></td></tr>
	<tr>
		<td>分数段</td>
		<td>60以下</td>
		<td>60~69</td>
		<td>70~79</td>
		<td>80~89</td>
		<td>90及以上</td>
	</tr>
	<tr>
		<td>人数</td>
		<td><?php echo ($fd[0]); ?></td>
		<td><?php echo ($fd[1]); ?></td>
		<td><?php echo ($fd[2]); ?></td>
		<td><?php echo ($fd[3]); ?></td>
		<td><?php echo ($fd[4]); ?></td>
	</tr>
</table>

</div>
<script type="text/javascript">
$(function(){
	$("#exam_analysis").addClass('active');
});
</script>
<style>
#examFooter, #examFooter ul li {
	background-color: #252525;
	text-align: center;
}
#examFooter .container {
	padding-top: 30px;
}
#examFooter ul li {
border: none;
color:white;
      font-size: 20px;
}
#examFooter ul li a , #examFooter p{
	font-size: 15px;
color : #959595;
}
</style>
<footer id="examFooter">
<div class="container">
<ul class="list-group col-md-4">
<li class="list-group-item">下载链接</li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/download/Firefox.exe">Firefox</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/download/codeblocks-12.11mingw-setup.exe">CodeBlocks for Win</a></li>
</ul>
<ul class="list-group col-md-4">
<li class="list-group-item">主站导航</li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/JudgeOnline/faqs.php">F.A.Qs</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/JudgeOnline/contest.php">Contest</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/JudgeOnline/ranklist.php">Ranklist</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/JudgeOnline/problemset.php">ProblemSet</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/JudgeOnline/stuexam/">Examination</a></li>
</ul>
<ul class="list-group col-md-3">
<li class="list-group-item">关于我们</li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/blog/">博客</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn/ranklist/">省赛排名</a></li>
<li class="list-group-item"><a href="http://acm.sdibt.edu.cn:8001">训练计划</a></li>
</ul>
<div class="col-md-offset-2 col-md-8">
<p>@Copyright&copy;SDIBT_ACM | Any Problems, Please Contact Admin:<a href="mailto:sdibtacm@126.com">admin</a></p>
</div>
</div>
</footer>
<script type="text/javascript" src="/JudgeOnline/stuexam/Public/Js/bootstrap.min.js"></script>
</body>
</html>