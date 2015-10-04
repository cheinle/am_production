<?php 

/* this will only log you out if a single user tries to log in again at a different location, not 
 * if another user logs in. So two users can log in if you have it. 2015/01/23
 */

	include('database_connection.php');
	///////////////////////////
	//change flag to true if you need to restrict database access (allows admin only)
	$database_down = 'false';

	/////////////////////////////////////////////////////////////////////////////////

	if($_POST) {
		
		$stmt1 = $dbc->prepare("SELECT * FROM users WHERE user_id = ? AND password = SHA1(?)");
		$stmt1 -> bind_param('ss', $_POST['email'],$_POST['password']);
				
	 	if ($stmt1->execute()){
	 		
	 		 $count_check = $stmt1->fetch();
             $size =sizeof($count_check);
			 //echo $size;
			 //check that one entry was returned
             if($size == 1) {
             	echo "workingggg";
              
			  	//go on to grab the old session id stored in the db
				$meta = $stmt1->result_metadata(); 
		   		while ($field = $meta->fetch_field()){ 
		        	$params[] = &$row[$field->name]; 
		    	} 
		
		    	call_user_func_array(array($stmt1, 'bind_result'), $params); 
			
				$old_session_id;
				$first_name;
				$last_name;
				$count = 0;
				$stmt1->execute(); //process is foward-curser so need to reset
				while($stmt1->fetch()){
					$count++;
					$old_session_id = $row['session_id'];
					$first_name = $row['first_name'];
					$last_name = $row['last_name'];
			   	}
	
				//store current session id
				if(session_id()){
					session_commit();
				}
				session_start();
				session_regenerate_id(true); 
				$new_session_id = session_id();
				session_commit();

				
				//check if old session id is the same as the new session id
				if($new_session_id != $old_session_id){
					
					//set new session id into the db and and destroy old session (so logs the other person out)
			
					//start old session and destroy it
					session_id($old_session_id);
					session_start();
					session_destroy();
					session_commit();
	
					session_id($new_session_id);
					session_start();
					
					$stmt2 = $dbc -> prepare("UPDATE users SET session_id = ? WHERE user_id = ?");
					$stmt2 -> bind_param('ss', $new_session_id,$_POST['email']);
					$stmt2 -> execute();
					$rows_affected2 = $stmt2 ->affected_rows;
					$stmt2 -> close();
						
					//check if add was successful or not. Tell the user
				    if($rows_affected2 < 0){;
						echo 'An error has occured here <br>';
						mysqli_error($dbc);		
					}
					$_SESSION['username'] = $_POST['email'];
					$_SESSION['session_id'] = $new_session_id;
					$_SESSION['first_name'] = $first_name;
					$_SESSION['last_name'] = $last_name;

						
					//if you need restrict access to database for any reason
					//destroy session for anyone who is not the developer
						
					if($database_down == 'true' || $database_down == 'moving'){
						if($_SESSION['username'] == $admin_user){
							header('Location: home_page.php');
						}
						else{
							session_destroy();
							//$path = $_SERVER['DOCUMENT_ROOT'].'/series/dynamic/airmicrobiomes/login.php';
							//header('Location: '.$path);
							//header('Location: login.php');
							header('Location: /series/dynamic/airmicrobiomes/login.php');
						}
					}
					else{
						header('Location: home_page.php');
					}
					exit;
				}
				else{//else stay logged on...this should never be true right now
					alert("Cassie Is Testing This. Please Notify Her If You See This");
					session_start();
					$_SESSION['username'] = $_POST['email'];
					$_SESSION['session_id'] = $new_session_id;
					session_destroy();//before this and your regenearte id...used to go here if stayed logged on. now goes above but still same problem
					echo $_SESSION['session_id'];
					echo $_SESSION['username'];
					header('Location: index.php');
					exit;
				}
	
			}
		}
	}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
	<meta charset="utf-8" />
	<title>Login Form</title>
	<meta content="width=device-width, initial-scale=1.0" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />
	<!-- BEGIN GLOBAL MANDATORY STYLES -->
	<link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
	<link href="assets/plugins/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" type="text/css"/>
	<link href="assets/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
	<link href="assets/css/style-metro.css" rel="stylesheet" type="text/css"/>
	<link href="assets/css/style.css" rel="stylesheet" type="text/css"/>
	<link href="assets/css/style-responsive.css" rel="stylesheet" type="text/css"/>
	<link href="assets/css/themes/default.css" rel="stylesheet" type="text/css" id="style_color"/>
	<link href="assets/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="assets/plugins/select2/select2_metro.css" />
	<!-- END GLOBAL MANDATORY STYLES -->
	<!-- BEGIN PAGE LEVEL STYLES -->
	<link href="assets/css/pages/login-soft.css" rel="stylesheet" type="text/css"/>
	<!-- END PAGE LEVEL STYLES -->
	<link rel="shortcut icon" href="favicon.ico" />
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="login">
	<!-- BEGIN LOGO -->
	<div class="logo">
		<!-- PUT YOUR LOGO HERE -->
	</div>
	<!-- END LOGO -->
	<!-- BEGIN LOGIN -->
	<div class="content">
		<!-- BEGIN LOGIN FORM -->
		<form class="form-vertical login-form" action="login.php" method="POST">
			<h3 class="form-title">Login to your account</h3>
			<div class="alert alert-error hide">
				<button class="close" data-dismiss="alert"></button>
				<span>Enter any username and password.</span>
			</div>
			<div class="control-group">
				<!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
				<label class="control-label visible-ie8 visible-ie9">Username</label>
				<div class="controls">
					<div class="input-icon left">
						<i class="icon-user"></i>
						<input class="m-wrap placeholder-no-fix" type="text" autocomplete="off" placeholder="Username" name="email"/>
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label visible-ie8 visible-ie9">Password</label>
				<div class="controls">
					<div class="input-icon left">
						<i class="icon-lock"></i>
						<input class="m-wrap placeholder-no-fix" type="password" autocomplete="off" placeholder="Password" name="password"/>
					</div>
				</div>
			</div>
			<div class="form-actions">
				<!--<label class="checkbox">
				<input type="checkbox" name="remember" value="1"/> Remember me
				</label>-->
				<button type="submit" class="btn blue pull-right">
				Login <i class="m-icon-swapright m-icon-white"></i>
				</button>            
			</div>
			<div class="forget-password">
				<h4>Forgot your password ?</h4>
				<p>
					no worries, click <!--<a href="/series/dynamic/airmicrobiomes/password_reset/reset_password.php">--><a href="javascript:;"  id="forget-password">here</a>
					to reset your password.
				</p>
			</div>
			<!--<div class="create-account">
				<p>
					Don't have an account yet ?&nbsp; 
					<a href="javascript:;" id="register-btn" >Create an account</a>
				</p>
			</div>-->
		</form>
		<!-- END LOGIN FORM -->        
		<!-- BEGIN FORGOT PASSWORD FORM -->
		<form class="form-vertical forget-form" action="/series/dynamic/airmicrobiomes/password_reset/forgot_passwordck.php" method="post">
			<h3 >Forget Password ?</h3>
			<p>Enter your e-mail address below to reset your password.</p>
			<div class="control-group">
				<div class="controls">
					<div class="input-icon left">
						<i class="icon-envelope"></i>
						<input class="m-wrap placeholder-no-fix" type="text" placeholder="Email" autocomplete="off" name="email" />
					</div>
				</div>
			</div>
			<div class="form-actions">
				<button type="button" id="back-btn" class="btn">
				<i class="m-icon-swapleft"></i> Back
				</button>
				<button type="submit" class="btn blue pull-right">
				Submit <i class="m-icon-swapright m-icon-white"></i>
				</button>            
			</div>
		</form>
		<!-- END FORGOT PASSWORD FORM -->
	</div>
	<!-- END LOGIN -->
	<!-- BEGIN COPYRIGHT -->
	<div class="copyright">
		2014 &copy; <a href="http://www.justukfreebies.co.uk/">Just UK Freebies</a> Login Form
	</div>
	<!-- END COPYRIGHT -->
	<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
	<!-- BEGIN CORE PLUGINS -->   <script src="assets/plugins/jquery-1.10.1.min.js" type="text/javascript"></script>
	<script src="assets/plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
	<!-- IMPORTANT! Load jquery-ui-1.10.1.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
	<script src="assets/plugins/jquery-ui/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>      
	<script src="assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="assets/plugins/bootstrap-hover-dropdown/twitter-bootstrap-hover-dropdown.min.js" type="text/javascript" ></script>
	<!--[if lt IE 9]>
	<script src="assets/plugins/excanvas.min.js"></script>
	<script src="assets/plugins/respond.min.js"></script>  
	<![endif]-->   
	<script src="assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
	<script src="assets/plugins/jquery.blockui.min.js" type="text/javascript"></script>  
	<script src="assets/plugins/jquery.cookie.min.js" type="text/javascript"></script>
	<script src="assets/plugins/uniform/jquery.uniform.min.js" type="text/javascript" ></script>
	<!-- END CORE PLUGINS -->
	<!-- BEGIN PAGE LEVEL PLUGINS -->
	<script src="assets/plugins/jquery-validation/dist/jquery.validate.min.js" type="text/javascript"></script>
	<script src="assets/plugins/backstretch/jquery.backstretch.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="assets/plugins/select2/select2.min.js"></script>
	<!-- END PAGE LEVEL PLUGINS -->
	<!-- BEGIN PAGE LEVEL SCRIPTS -->
	<script src="assets/scripts/app.js" type="text/javascript"></script>
	<script src="assets/scripts/login-soft.js" type="text/javascript"></script>      
	<!-- END PAGE LEVEL SCRIPTS --> 
	<script>
		jQuery(document).ready(function() {     
		  App.init();
		  Login.init();
		});
	</script>
	<!-- END JAVASCRIPTS -->
	<div style="position:absolute; bottom:0px; left:0px; "><a href="http://www.justukfreebies.co.uk/website-templates/free-responsive-login-form-template/">Free Website Templates</a></div>
</body>
<!-- END BODY -->
</html>