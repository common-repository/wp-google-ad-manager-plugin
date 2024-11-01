<?php

/*
Plugin Name: WP Google Ad Manager Plugin
Plugin URI: http://www.bretteleben.de/lang-en/wordpress/google-anzeigenmanager-plugin.html
Description: A simple Wordpress plugin to use Google Ad Mananger for your Ads.
Version: 1.1.0
Author: Andreas Berger
Author URI: http://www.bretteleben.de/
*/

/*
Copyright 2009  Andreas Berger  (email : andreas_berger@bretteleben.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$wp_gam_version = "1.1.0";

//modify output
function begam_callback($begam_buffer) {
	//exclude admin pages and feeds
	if(!is_admin()&&!is_feed()&&!is_404()){
		//get pubid and slots from db
		$begam_pubid=trim(get_option('wp_gam_pubid'));
		if($begam_pubid==""){$begam_pubid="No Google Ad Mananger PubID set!";}
		$_begam_slotvalues=array();
		for($i=0;$i<=6;$i++){
			$_begam_slotvalues["S".$i]=trim(get_option('wp_gam_slotid_'.$i));
		}
		for($i=7;$i<=9;$i++){
			$_begam_slotvalues_temp=explode(",", get_option('wp_gam_slotid_'.$i));
			for($j=0;$j<=count($_begam_slotvalues_temp)-1;$j++){$_begam_slotvalues_temp[$j]=trim($_begam_slotvalues_temp[$j]);}
			$_begam_slotvalues["S".$i]=$_begam_slotvalues_temp;
		}
		//lets have a look what we have got on this page
		$begam_slots = array();
		$gamattr = array();
		$gampageattr = array();
		$gamslotattr = array();
		if (preg_match_all("#(<|&lt;)!(&\#45;|&ndash;|&minus;|&\#8722;|&\#8211;|-)+ begam\{(S[0-9])(.*?)\} (&\#45;|&ndash;|&minus;|&\#8722;|&\#8211;|-)+(>|&gt;)#s", $begam_buffer, $matches, PREG_PATTERN_ORDER) > 0) {
			foreach ($matches[0] as $match) {
				$_gamstring = explode("{", $match);
				$_gamstring = explode("}", $_gamstring[1]);
				$_gamstring = explode("/", $_gamstring[0]);
				//lets look what corresponds to the found pointer
				$_begam_fillvalue = "empty";
				if(is_array($_begam_slotvalues[$_gamstring[0]])){
					if(count($_begam_slotvalues[$_gamstring[0]])>=1){
						$_begam_fillvalue=trim(array_shift($_begam_slotvalues[$_gamstring[0]]));
					}
				}
				else{
					$_begam_fillvalue = $_begam_slotvalues[$_gamstring[0]];
				}
				if($_begam_fillvalue==""){$_begam_fillvalue = "empty";}
				if($_begam_fillvalue!="empty"){
					//we have a slot, lets look if it comes with additional attributs
					//ad-attributes a.k. targeting
					if(count($_gamstring)>=2&&$_gamstring[1]!=""){
						$_attrparts = explode(";", $_gamstring[1]);
						for($i=0;$i<=count($_attrparts)-1;$i++){
							$_attrsmallparts = explode(",", $_attrparts[$i]);
							$gamattr[]="GA_googleAddAttr(\"".trim($_attrsmallparts[0])."\", \"".trim($_attrsmallparts[1])."\");";
						}
					}
					//page+slot-attributes
					if(count($_gamstring)>=3){
						$_psparts = explode(";", $_gamstring[2]);
						for($i=0;$i<=count($_psparts)-1;$i++){
							$_pssmallparts = explode(",", $_psparts[$i]);
							if(count($_pssmallparts)==2){
								$gampageattr[]="GA_googleAddAdSensePageAttr(\"".trim($_pssmallparts[0])."\", \"".trim($_pssmallparts[1])."\");";
							}
							elseif(count($_pssmallparts)==3){
								$gamslotattr[]="GA_googleAddAdSenseSlotAttr(\"".trim($_begam_fillvalue)."\", \"".trim($_pssmallparts[1])."\", \"".trim($_pssmallparts[2])."\");";
							}
						}
					}
					//all done, publish the slot
					$begam_slots[]="GA_googleAddSlot(\"".trim($begam_pubid)."\", \"".trim($_begam_fillvalue)."\");\n";
					$fillslot="\n<script type=\"text/javascript\">GA_googleFillSlot(\"".trim($_begam_fillvalue)."\");</script>\n";
				}
				else {
					$fillslot="\n<!-- slot ".$_gamstring[0]." not found -->\n";
				}
				$begam_buffer = preg_replace( '#'.$match.'#s', $fillslot , $begam_buffer , 1 );
			}
		}
		if(count($begam_slots)>=1){
			//remove duplicates
			$gamattr = array_unique($gamattr);
			$gampageattr = array_unique($gampageattr);
			$gamslotattr = array_unique($gamslotattr);
			//start to collect head-scripts
			$wp_begam_headinject="<script type=\"text/javascript\" src=\"http://partner.googleadservices.com/gampad/google_service.js\">\n";
			$wp_begam_headinject.="</script>\n";
			$wp_begam_headinject.="<script type=\"text/javascript\">\n";
			$wp_begam_headinject.="  GS_googleAddAdSenseService(\"".$begam_pubid."\");\n";
			$wp_begam_headinject.="  GS_googleEnableAllServices();\n";
			$wp_begam_headinject.="</script>\n";
			//process attributes
			if(count($gamattr)>=1){
			$wp_begam_headinject.="<script type=\"text/javascript\">\n";
				for($i=0;$i<=count($gamattr)-1;$i++){
					$wp_begam_headinject.="  ".trim($gamattr[$i])."\n";
				}
			$wp_begam_headinject.="</script>\n";
			}
			//process ad slots
			$wp_begam_headinject.="<script type=\"text/javascript\">\n";
				for($i=0;$i<=count($begam_slots)-1;$i++){
					$wp_begam_headinject.="  ".trim($begam_slots[$i])."\n";
				}
			//process pageattributes
			if(count($gampageattr)>=1){
				for($i=0;$i<=count($gampageattr)-1;$i++){
					$wp_begam_headinject.="  ".trim($gampageattr[$i])."\n";
				}
			}
			//process slotattributes
			if(count($gamslotattr)>=1){
				for($i=0;$i<=count($gamslotattr)-1;$i++){
					$wp_begam_headinject.="  ".trim($gamslotattr[$i])."\n";
				}
			}
			$wp_begam_headinject.="</script>\n";
			$wp_begam_headinject.="<script type=\"text/javascript\">\n";
			$wp_begam_headinject.="  GA_googleFetchAds();\n";
			$wp_begam_headinject.="</script>\n";
			//replace placeholder
			$begam_buffer = preg_replace( "#<!-- begam_placeholder -->#s", $wp_begam_headinject , $begam_buffer);
		}
	}
  return $begam_buffer;
}
//buffer output
function begam_buffer_start() { ob_start("begam_callback"); }
//release output
function begam_buffer_end() { ob_end_flush(); }
//provide placeholder in head
function begam_add_placeholder_to_head() { echo "<!-- begam_placeholder -->\n"; }

//add options page to menu
function begam_add_option_page_to_menu() {
	if (function_exists('add_options_page')) {
		add_options_page('Google Ad Manager', 'Google Ad Manager', 8, __FILE__, 'begam_options_page');
	}
}
//options page
function begam_options_page() {
global $wp_gam_version;

echo"<div class='wrap'>\n";
echo"<h2>WP Google Ad Manager Plugin v ".$wp_gam_version." Options</h2>\n";
echo"<p>For information and updates, please visit:<br />\n";
echo"<a href='http://www.bretteleben.de' target='_blank'>http://www.bretteleben.de</a></p>\n";
echo"<p>This plugin allows to use Google Ad Manager to display Ads within your posts, pages and sidebar.</p>\n";
echo"<form method='post' action='options.php'>\n";
settings_fields('wp_gam-options'); //1.1
//wp_nonce_field('update-options');
echo"<table class='form-table'>\n";
echo"<tr><th colspan='2' style='background-color:#ddd'><strong>Setup</strong></th></tr>\n";
echo"<tr valign='top'><td colspan='2'>First step is to set the Adsense Publisher ID, assoziated with your Ad Manager Account. <br />You find it (for example) in your Adsense Account at 'Your Account' > 'Settings' near the bottom of the page. It is the Identity for Content Pages.</td></tr>\n";
echo"<tr valign='top'>\n";
echo"<th scope='row'>Pub-ID:</th>\n";
echo"<td><input type='text' name='wp_gam_pubid' size='30' value='".get_option("wp_gam_pubid")."' /></td>\n";
echo "</tr>\n";
echo"<tr valign='top'><td colspan='2'>Next, set the Ad Manager Slots, you want to use at your blog. Please enter 1 SlotName per field for the fields 0-6.<br />They are called from the plugin by there Names 'S0', 'S1, and so on.'</td></tr>\n";
for($i=0;$i<=6;$i++){
	echo"<tr valign='top'>\n";
	echo"<th scope='row'>(Slotname) S".$i.":</th>\n";
	echo"<td><input type='text' name='wp_gam_slotid_".$i."' size='100' maxlength='100' value='";
	echo get_option("wp_gam_slotid_".$i);
	echo"' /></td>\n";
	echo"</tr>\n";
}
echo"<tr valign='top'><td colspan='2'>Fields 7-9 allow to set as many slotnames you want, separated by commas.<br />What is it for?<br />When the plugin calls one of these Arrays - referenced by there names 'S7', 'S8' or 'S9' - it will use each of the given Slots one time per page.<br />Use these fields when calling ads from within a post or from a position in your template, that is called more then one time per page.</td></tr>\n";
for($i=7;$i<=9;$i++){
	echo"<tr valign='top'>\n";
	echo"<th scope='row'>(Array of Slotnames) S".$i.":</th>\n";
	echo"<td><textarea name='wp_gam_slotid_".$i."' rows='6' cols='100'>";
	echo get_option("wp_gam_slotid_".$i);
	echo"</textarea>\n";
	echo"</td></tr>\n";
}
echo "</table>\n";
echo "<input type='hidden' name='action' value='update' />\n";
echo "<input type='hidden' name='page_options' value='wp_gam_pubid,";
for($i=0;$i<=9;$i++){
	echo "wp_gam_slotid_".$i;
		if($i<=8){
			echo ",";
		}
	}
echo "' />\n";
echo "<p class='submit'>\n";
echo "<input type='submit' name='Submit' value='";
echo _e('Save Changes');
echo "' />\n";
echo "</p></form>\n\n";
echo "<p>";
?>
<table class="form-table">
	<tr>
		<th style="background-color:#ddd">
			<strong>General Usage</strong>
		</th>
	</tr>
	<tr>
		<td>
			<strong>Template:</strong><br />
			To call ads from within your template (above or below content, at a fixed position in your sidebar, and so on ...) insert the call directly into the sourcecode of the template.<br />The call comes as HTML-comment and looks such as:<br />
			<strong><i>&lt;!-- begam{field_to_use} --&gt;</i></strong><br /> where "field_to_use" is one of the fields above: S0 - S9, so - to call field S1, the call would look such as: <i>&lt;!-- begam{S1} --&gt;</i>
		</td>
	</tr>
	<tr>
		<td>
			<strong>Posts:</strong><br />
			To call ads from within a post, switch to HTML-view and insert the call at the position, you want the ad to be displayed within your post.<br />The call comes as HTML-comment and looks such as:<br />
			<strong><i>&lt;!-- begam{field_to_use} --&gt;</i></strong><br /> where "field_to_use" is one of the fields above: S0 - S9, so - to call field S7, the call would look such as: <i>&lt;!-- begam{S7} --&gt;</i><br />You may want to use the fields 7 to 9 with your posts, because they allow to set more than one slot. How many slots you set in these fields is up to you. Set 3 slots and the first three posts will display the ads. Set 5, 7, 10 ... exactly. :)
		</td>
	</tr>
	<tr>
		<td>
			<strong>Sidebar:</strong><br />
			To place ads in your sidebar, ad a Text-widget (for "Arbitrary text or HTML" - it comes with wordpress) to your sidebar and put into it the call, which again looks such as:<br />
			<strong><i>&lt;!-- begam{field_to_use} --&gt;</i></strong><br /> where "field_to_use" is one of the fields above: S0 - S9, so - to call field S3, the call would look such as: <i>&lt;!-- begam{S3} --&gt;</i>
		</td>
	</tr>
	<tr>
		<td style="background-color:#ddd">
			<strong>Ad Manager Attributs</strong><br />
		The plugin comes with full support for Google Ad Manager Custom Targeting, PageLevel- and SlotLevel-Attributes.<br />
		For Information on how to use them have a look at the readme.txt or visit <a href="http://www.bretteleben.de/lang-en/wordpress/google-anzeigenmanager-plugin.html" target="_blank">bretteleben.de</a>.
		</td>
	</tr>
</table>
<br /><br />That's it - have fun.<br />

<?php
echo "</p>\n</div>\n\n";
}

//register options 1.1
function begam_register_settings() {
	register_setting( 'wp_gam-options', 'wp_gam_pubid' );
	for($i=0;$i<=9;$i++){
		register_setting( 'wp_gam-options', 'wp_gam_slotid_'.$i );
		}		
	}

add_action('admin_init', 'begam_register_settings' ); //1.1 register options
add_action('admin_menu', 'begam_add_option_page_to_menu' );
add_action('wp_head', 'begam_add_placeholder_to_head' );
add_action('init', 'begam_buffer_start' );
add_action('shutdown', 'begam_buffer_end');
?>