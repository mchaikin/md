<style>
	/* Move login to center of screen */
	.login-form {
		position: absolute;
		left: 50%;
		top: 25%;
		margin-left: -200px;
		width: 400px;
	}

	@media (max-width: 768px) {
		.login-form {
			top: 5%;
		}
	}

	.login-container {
		background: #fff;
		overflow: hidden;
		border-radius: 3px;
		box-shadow: 0 1px 8px rgba(0,0,0,0.1);
		margin-bottom: 10px;
	}

	.login-content {
		padding: 10px 20px;
	}

	.login-header {
		color: #fff;
		background: #55606e;
		padding: 15px 15px;
		margin: 0 0 10px;
	}

	.login-header h2 {
		text-align: center;
		margin: 0;
		padding: 0;
		font-size: 18px;
	}

	.form-group {
		margin: 0 0 15px;
	}

	.btn-success {
		padding: 8px 20px;
		background: #24afb2;
	}

	.btn-success:hover {
		background: #24bec1;
	}

	.form-control {
		background: #f1f1f1;
		border-color: #f1f1f1;
		box-shadow: none;
	}

	.form-control:focus {
		border-color: #f1f1f1;
	}

	.divider {
		margin: 10px 0;
	}
</style>

<section class="col-sm-6 login-form">

	<form class="form-horizontal" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<div class="login-container">

			<div class="login-header">
				<h2>Who's there?</h2>
			</div>

			<div class="login-content">
				<?php

					// Redirect signed users
					if ( $_SESSION['a-login'] === true ) {
						header("Location: ?s=home");
						exit;
					}


					// Handle login post
					if ( isset($_POST['submit']) ) {

						// Clear login trys after the last attempt expired
						if ( $_SESSION['auth-ban']['last-try'] < strtotime('-30 minutes') ) {
							unset($_SESSION['auth-ban']['trys']);
						}

						// Check credentials
						if ( $_SESSION['auth-ban']['trys'] >= 3 AND $_SESSION['auth-ban']['last-try'] > strtotime('-30 minutes') ) {

							writeLog('auth.bans', "User with IP \"{$_SERVER['REMOTE_ADDR']}\" has failed to authorize for more than 3 times!", './../tmp/logs/');
							echo '<div class="text-red">You have entered invalid username or password more than <b>3</b> times. Your IP has been logged and you have been blocked!</div><div class="divider"></div>';

						} else if ( $_POST['username'] != $settings['admin_user'] OR hash(SHA512, $_POST['password']) != $settings['admin_pass'] ) {

							$attempts = $_SESSION['auth-ban']['trys'] += 1;
							$_SESSION['auth-ban']['last-try'] = time();

							echo '<div class="text-red">Invalid username or password (<b>' . (4 - $attempts) . ' attempts remaining)</b>.</div><div class="divider"></div>';

						} else { // Login

							unset($_SESSION['auth-ban']);
							$_SESSION['a-login'] = true;
							header("Location: ?s=home");
							exit;

						}

					}

				?>

				<div class="form-group">
					<div class="input-prepend">
						<div class="prepend"><i class="fa fa-user"></i></div>
						<input type="text" name="username" class="form-control" placeholder="Username" id="username" autofocus required>
					</div>
				</div>

				<div class="form-group">
					<div class="input-prepend">
						<div class="prepend"><i class="fa fa-key"></i></div>
						<input type="password" name="password" class="form-control" placeholder="Password" id="password" autocomplete="off" required>
					</div>
				</div>

				<div class="divider"></div>
				<button type="submit" name="submit" value="sign-in" class="btn btn-success pull-right">Sign in</button>
				<a title="How do I reset password?" target="_blank" href="http://codecanyon.net/item/aio-radio-station-player-shoutcast-and-icecast/10576010/faqs/23932">Forgot password?</a>
				<div class="clearfix"></div>

			</div>
		</div>

		<div class="text-center">Version: <b><?php echo ((is_file('version.txt')) ? file_get_contents('version.txt') : 'n/a'); ?></b></div>

	</form>
</section>