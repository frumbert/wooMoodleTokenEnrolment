<?php require_once(ABSPATH . 'wp-includes/pluggable.php'); ?>
<div class="wrap">
<h2><?php print WMTE_PUGIN_NAME ." ". WMTE_CURRENT_VERSION ?></h2>
<p>A plugin designed to allow a wooCommerce/MarketPress digital download to generate enrolment tokens using the Moodle Token Enrolment plugins.</p>
<p>Make a plain text file called <code>yourname-wmte.txt</code> (must end with -wmte.txt) and enter at least a course value (one value per line). Possible values are:</p>
<ul style="list-style-type: disc; list-style-position: inside">
<li><b>course</b>: the IDNUMBER of the course in Moodle (this is NOT the row id)</li>
<li><b>cohort</b>: the IDNUMBER of the cohort in Moodle that users of this token will be added to (created automatically if it's not found). See the plugin README for more info ...</li>
<li><b>seats</b>: the actual number of tokens you want to generate, from 1 to 500 (default: 1)</li>
<li><b>places</b>: the number of times each token can be used, from 1 to 500 (default: 1)</li>
<li><b>expiry</b>: a unix_timestamp which represents the date that the tokens are no longer considered valid (default: zero for never)</li>
<li><b>prefix</b>: a 0-4 letter prefix you want your tokens to start with (default: empty)</li>
</ul>
<p>Your file should look something like this:</p>
<p><pre style='border:1px solid rgba(0,0,0,.5);background-color:rgba(255,255,255,.5);padding:10px;'>
course=myCourse2
seats=10
prefix=free
</pre></p>
<p>You are <b>strongly urged</b> to set the <code>Download Limit</code> and <code>Download Expiry</code> to <i>1</i> so the customer doesn't re-generate the tokens multiple times, as well as setting <code>Sold Individually</code> so that there is no quanitity option.</p>

<h2>Settings</h2>
<form method="post" action="options.php">
    <?php
		settings_fields( 'wmte-settings-group' );
	?>
    <table class="form-table">
        <tr valign="top">
	        <th scope="row">Moodle Root URL</th>
	        <td><input type="text" name="wmte_moodle_url" value="<?php echo get_option('wmte_moodle_url'); ?>" size="60" />
	        <div class="description">please OMIT the trailing slash.</div>
	        </td>
        </tr>

        <tr valign="top">
    	    <th scope="row">Webservice Token</th>
        	<td><input type="text" name="wmte_webservice_token" value="<?php echo get_option('wmte_webservice_token'); ?>" size="60" />
        	<div class="description">Use the Moodle Webservice Token function on your site to generate one.</div>
        	</td>
        </tr>

    </table>

    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
