<?php
add_shortcode('zawiw_registration', 'zawiw_registration_shortcode');
add_action( 'wp_enqueue_scripts', 'zawiw_registration_queue_script' );
add_action( 'wp_enqueue_scripts', 'zawiw_registration_queue_stylesheet' );
register_activation_hook( dirname( __FILE__ ).'/zawiw-registration.php', 'zawiw_registration_activation');

function zawiw_registration_shortcode($param)
{
		$error = 0;
        if(isset($_POST['submit']))
        {
                if(!isset($_POST['username']) || $_POST['username'] == "")
                {
                        $error = $error | 1;
                }
                if(!isset($_POST['email']) || $_POST['email'] == "" || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
                {
                        $error = $error | 2;
                }
                if(!isset($_POST['fullname']) || $_POST['fullname'] == "")
                {
                        $error = $error | 4;
                }
                if(!isset($_POST['mailto']) || $_POST['mailto'] == "")
                {
                        $error = $error | 8;
                }
                if($error == 0)
                {
                	$error = 0;
                	/* Load Trashmails */
                	$trashmailHandle = curl_init();
					curl_setopt($trashmailHandle, CURLOPT_URL, "http://www.email-wegwerf.de/trashmailliste.txt");
					curl_setopt($trashmailHandle, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($trashmailHandle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6');
					$trashmail = curl_exec($trashmailHandle);
					curl_close($trashmailHandle);
					$mailadress = substr($_POST['email'], strrpos($_POST['email'], "@")+1);
					/* Trashmail resources loaded */
					/* Test EMail via stopforumspam API */
					$spammailHandle = curl_init();
					curl_setopt($spammailHandle, CURLOPT_URL, "http://api.stopforumspam.org/api?email=" . $_POST['email']);
					curl_setopt($spammailHandle, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($spammailHandle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6');
					$spamtest = curl_exec($spammailHandle);
					curl_close($spammailHandle);
					/* EMail check resource loaded */
					if(preg_match('/<appears>yes<\/appears>/', $spamtest) === 0 && preg_match('/' . $mailadress . '/', $trashmail) === 0)
					{
	                    $msg = "Der Benutzer " . $_POST['fullname'] . " möchte einen Benutzeraccount für " . $_SERVER['HTTP_HOST'] . " mit folgenden Daten haben:\r\n";
	                    $msg .= "Username:\t" . $_POST['username'] . "\r\n";
	                    $msg .= "Name:\t\t" . $_POST['fullname'] . "\r\n";
	                    $msg .= "EMail:\t\t" . $_POST['email'] . "\r\n\r\n";
	                    $msg .= "Diese EMail wurde automatisch generiert.\r\n";
	                    $head = "From: " . $_POST['email'] . "\r\n\r\n";
	                    if(mail($_POST['mailto'], "Neue Benutzerregistrierung angefragt", $msg, $head) === TRUE)
	                        echo "<div class='success'>Ihr Antrag wurde erfolgreich vermittelt</div>";
	                    else
	                        echo "<div class='warning'>Bitte nochmal versuchen, Ihr Antrag konnte nicht vermittelt werden</div>";
	                    return;
					}
					else
					{
						global $wpdb;
						$wpdb->insert('zawiw_registration_spam_counter', array('blogPrefix' => $wpdb->get_blog_prefix(), 'spamMail' => $_POST['email']));
						//is spammer, still gets positive feedback
						echo "<div class='success'>Ihr Antrag wurde erfolgreich vermittelt</div>";
						return;
					}
                }
        }
        if(!isset($param['mailto']) || !filter_var($param['mailto'], FILTER_VALIDATE_EMAIL))
        {
                echo "<h2>Keine valide E-Mail-Adresse als Empfänger angegeben</h2>";
                return;
        }
        
        ?>
        <div id="zawiw_registration_form">
	        <form method="post" action="" enctype="multipart/form-data">
	                <p class="clear">
	                        <table class="form-table">
	                                <tbody>
	                                        <tr>
	                                                <th scope="row">
	                                                        <label for="username">Benutzername <sup>*</sup></label>
	                                                </th>
	                                                <td>
	                                                       <input <?php echo (isset($error) && (($error & 1) > 0) ? "class='error'" : "") ?> type="text" placeholder="mmustermann" name="username" id="username" />
	                                                </td>   
	                                        </tr>
	                                        <tr>
	                                                <th scope="row">
	                                                        <label for="email">E-Mail-Adresse <sup>*</sup></label>
	                                                </th>
	                                                <td>
	                                                        <input <?php echo (isset($error) && (($error & 2) > 0) ? "class='error'" : "") ?>  type="text" placeholder="mustermann@example.com" name="email" id="email" />
	                                                </td>
	                                        </tr>
	                                        <tr>
	                                                <th scope="row">
	                                                        <label for="fullname">Name <sup>*</sup></label>
	                                                </th>
	                                                <td>
	                                                        <input <?php echo (isset($error) && (($error & 4) > 0) ? "class='error'" : "") ?>  type="text" placeholder="Max Mustermann" name="fullname" id="fullname" />
	                                                </td>
	                                        </tr>
	                                </tbody>
	                        </table>
	                </p>
	                <p>
	                	<label><sup>*</sup> Pflichtfelder</label>
	                </p>
	                <input type="hidden" name="mailto" value="<?php if(isset($param['mailto'])) echo $param['mailto'] ?>" />
	                <p class="clear">
	                        <input type="submit" value="Registrierungsantrag abschicken" name="submit" id="submit" class="button-primary" />
	                </p>
	        </form>
	    </div>

        <?php
}

function zawiw_registration_queue_script()
{
        global $post;   //Contains the whole site content
        if(!has_shortcode($post->post_content, 'zawiw_registration'))   //Loads stylesheets only if shortcode exists
                return;
        wp_enqueue_script( 'jquery' );
}

function zawiw_registration_queue_stylesheet() {
        global $post;   //Contains the whole site content
        if(!has_shortcode($post->post_content, 'zawiw_registration'))   //Loads stylesheets only if shortcode exists
                return;
        wp_enqueue_style( 'zawiw_registration_style', plugins_url( 'style.css', __FILE__ ) );
}

?>