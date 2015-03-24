<?php
add_shortcode('zawiw_registration', 'zawiw_registration_shortcode');
add_action( 'wp_enqueue_scripts', 'zawiw_registration_queue_script' );
add_action( 'wp_enqueue_scripts', 'zawiw_registration_queue_stylesheet' );

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
	                                                        <label for="username">Benutzername (*)</label>
	                                                </th>
	                                                <td>
	                                                       <input <?php echo ((($error & 1) > 0) ? "class='error'" : "") ?> type="text" placeholder="mmustermann" name="username" id="username" />
	                                                </td>   
	                                        </tr>
	                                        <tr>
	                                                <th scope="row">
	                                                        <label for="email">E-Mail-Adresse (*)</label>
	                                                </th>
	                                                <td>
	                                                        <input <?php echo ((($error & 2) > 0) ? "class='error'" : "") ?>  type="text" placeholder="mustermann@example.com" name="email" id="email" />
	                                                </td>
	                                        </tr>
	                                        <tr>
	                                                <th scope="row">
	                                                        <label for="fullname">Name (*)</label>
	                                                </th>
	                                                <td>
	                                                        <input <?php echo ((($error & 4) > 0) ? "class='error'" : "") ?>  type="text" placeholder="Max Mustermann" name="fullname" id="fullname" />
	                                                </td>
	                                        </tr>
	                                </tbody>
	                        </table>
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