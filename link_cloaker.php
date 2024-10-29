<?php

/**
 * Plugin Name: Affiliate Cloaker
 * Plugin URI: http://m7tech.net/wordpress-affiliate-cloaker/
 * Author URI: http://m7tech.net/
 * Version: 0.2
 * Description: Generates redirect Javascript code on specific pages or 
 * posts that will redirect a user but not a spider. Used to cloak pages
 * to go to affiliate offers.
 * Author: Elliot (info@m7tech.net) 
**/

class link_cloaker {
  
  function link_cloaker() {
    
  }
  
  /**
   * Inserts cloak code into the post/page.
   **/
    
  function wp_head() {
    if( is_feed() ) {
      return;
    }
    
    global $wp_query;
    $post = $wp_query->get_queried_object();
    
    // Figure out if we need to cloak or not.
    if( is_single() || is_page() ) {
      $cloak_needed = htmlspecialchars( stripcslashes( get_post_meta( $post->ID, 'is_cloaked', true ) ) );
      if( !$cloak_needed ) {
	return;
      } else {
	$dest = $this->get_cloak_destination( $post );
	if( $dest != '' ) {
	  $cloak_code = $this->generate_cloak( $dest );
	  echo( $cloak_code );
	}
      }
    }
  }
  
  function get_cloak_destination( $post ) {
    $ret = '';
    $dest = trim( stripcslashes( get_post_meta( $post->ID, 'cloak_destination', true ) ) );
    if( $dest ) {
      $ret = $dest;
    }
    return $ret;
  }
    
  function generate_cloak( $destination ) {
    $ret = '<script language="JavaScript">'."\n";
    $fs = $this->encode( "<FRAMESET cols='100%'><FRAMESET rows='100%'><FRAME src='$destination'></FRAMESET></FRAMESET>" );
    $ret .= "var s = unescape(\"$fs\");\n";
    $ret .= "document.write(s);\n";
    $ret .= "</script>\n";
    return $ret;
  }
    
  function encode( $str ) {
    $ret = '';
    for( $i=0; $i<strlen( $str ); $i++ ) {
      $ret .= '%'.dechex( ord( $str[ $i ] ) );
    }
    return $ret;
  }
    
  function edit_cloak( $id ) {
    $cloak_changed = $_POST[ 'edit_cloak' ];
    if( isset( $cloak_changed ) && !empty( $cloak_changed ) ) {
      $is_cloaked = $_POST[ 'is_cloaked' ];
      $cloak_destination = $_POST[ 'cloak_destination' ];
      if( !preg_match( '/^http:\/\//', $cloak_destination ) ) {
        $cloak_destination = 'http://'.$cloak_destination;
      }

      delete_post_meta( $id, 'is_cloaked' );
      delete_post_meta( $id, 'cloak_destination' );

      if( isset( $is_cloaked ) && !empty( $is_cloaked ) ) {
	add_post_meta( $id, 'is_cloaked', $is_cloaked );
      }
            
      if( isset( $cloak_destination ) && !empty( $cloak_destination ) ) {
	add_post_meta( $id, 'cloak_destination', $cloak_destination );
      }

    }
  }
    
  function cloak_ui() {
    global $post;
    $post_id = $post;
    if( is_object( $post_id ) ) {
      $post_id = $post_id->ID;
    }
    $is_cloaked = htmlspecialchars( stripcslashes( get_post_meta( $post_id, 'is_cloaked', true ) ) );
    $cloak_destination = htmlspecialchars( stripcslashes( get_post_meta( $post_id, 'cloak_destination', true ) ) );
    if( $is_cloaked ) {
      $is_cloaked = ' checked="1"';
    } else {
      $is_cloaked = '';
    }
    if (substr($this->wp_version, 0, 3) >= '2.5') {
      echo( '<div id="wpcloak" class="postbox closed">' );
      echo( '<h3>Link Cloaker</h3>' );
      echo( '<div class="inside">' );
      echo( '<div id="link_cloak">' );
    } else {
      echo( '<div class="dbx-b-ox-wrapper">' );
      echo( '<fieldset id="cloakdiv" class="dbx-box">' );
      echo( '<div class="dbx-h-andle-wrapper">' );
      echo( '<h3 class="dbx-handle">Link Cloaker</h3>' );
      echo( '</div>' );
      echo( '<div class="dbx-c-ontent-wrapper">' );
      echo( '<div class="dbx-content">' );
    }
    echo( '<input value="edit_cloak" type="hidden" name="edit_cloak" />' );
    $table = '
      <table style="margin-bottom:40px">
      <tr>
      <th style="text-align:left;" colspan="2">
      </th>
      </tr>
      <tr>
      <th scope="row" style="text-align:right;">Cloaked?</th>
      <td><input type="checkbox" name="is_cloaked" '.$is_cloaked.'/></td>
      </tr>
      <tr>
      <th scope="row" style="text-align:right;">Destination URL:</th>
      <td><input value="'.$cloak_destination.'" type="text" name="cloak_destination" size="60"/></td>
      </tr>
      </table>';
    echo( $table );
    if (substr($this->wp_version, 0, 3) >= '2.5') { 
      echo( "</div></div></div>" );
    } else {
      echo( "</div></fieldset></div>" );
    } 
  }
  }

$lc = new link_cloaker();
add_action( 'wp_head', array( $lc, 'wp_head' ) );

add_action('edit_post', array( $lc, 'edit_cloak' ) );
add_action('publish_post', array( $lc, 'edit_cloak' ) );
add_action('save_post', array( $lc, 'edit_cloak' ) );
add_action('edit_page_form', array( $lc, 'edit_cloak' ) );

if (substr($aiosp->wp_version, 0, 3) >= '2.5') {
  add_action( 'edit_form_advanced', array( $lc, 'cloak_ui' ) );
  add_action( 'edit_page_form', array( $lc, 'cloak_ui' ) );
 } else {
  add_action( 'dbx_post_advanced', array($lc, 'cloak_ui' ) );
  add_action( 'dbx_page_advanced', array($lc, 'cloak_ui' ) );
 }

?>
