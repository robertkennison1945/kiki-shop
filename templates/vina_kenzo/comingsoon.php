<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2015 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

defined('_JEXEC') or die;
$doc = JFactory::getDocument();
$app = JFactory::getApplication();

require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

$twofactormethods = UsersHelper::getTwoFactorMethods();
//Load Helix
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';

if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $this->helix3 = Helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

//Body Font
$webfonts = array();

if( $this->params->get('enable_body_font') ) {
    $webfonts['body'] = $this->params->get('body_font');
}

//Heading1 Font
if( $this->params->get('enable_h1_font') ) {
    $webfonts['h1'] = $this->params->get('h1_font');
}

//Heading2 Font
if( $this->params->get('enable_h2_font') ) {
    $webfonts['h2'] = $this->params->get('h2_font');
}

//Heading3 Font
if( $this->params->get('enable_h3_font') ) {
    $webfonts['h3'] = $this->params->get('h3_font');
}

//Heading4 Font
if( $this->params->get('enable_h4_font') ) {
    $webfonts['h4'] = $this->params->get('h4_font');
}

//Heading5 Font
if( $this->params->get('enable_h5_font') ) {
    $webfonts['h5'] = $this->params->get('h5_font');
}

//Heading6 Font
if( $this->params->get('enable_h6_font') ) {
    $webfonts['h6'] = $this->params->get('h6_font');
}

//Navigation Font
if( $this->params->get('enable_navigation_font') ) {
    $webfonts['.sp-megamenu-parent'] = $this->params->get('navigation_font');
}

//Custom Font
if( $this->params->get('enable_custom_font') && $this->params->get('custom_font_selectors') ) {
    $webfonts[ $this->params->get('custom_font_selectors') ] = $this->params->get('custom_font');
}

$this->helix3->addGoogleFont($webfonts);

$comingsoon_title = $this->params->get('comingsoon_title');
if( $comingsoon_title ) {
	$doc->setTitle( $comingsoon_title . ' | ' . $app->get('sitename') );
}

$comingsoon_date = explode('-', $this->params->get("comingsoon_date"));

//Load jQuery
JHtml::_('jquery.framework');
?>
<!DOCTYPE html>
<html class="sp-comingsoon" xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    if($favicon = $this->helix3->getParam('favicon')) {
        $doc->addFavicon( JURI::base(true) . '/' .  $favicon);
    } else {
        $doc->addFavicon( $this->helix3->getTemplateUri() . '/images/favicon.ico' );
    }
    ?>
    <jdoc:include type="head" />
    <?php
	$preloader_bg  = ($this->helix3->getParam('preloader_bg')) ? $this->helix3->getParam('preloader_bg') : '#f5f5f5';
    $preloader_tx  = ($this->helix3->getParam('preloader_tx')) ? $this->helix3->getParam('preloader_tx') : '#f5f5f5';
    $this->helix3->addCSS('bootstrap.min.css, font-awesome.min.css')
        ->lessInit()->setLessVariables(array(
            'preset'=>$this->helix3->Preset(),
            'bg_color'=> $this->helix3->PresetParam('_bg'),
            'text_color'=> $this->helix3->PresetParam('_text'),
            'major_color'=> $this->helix3->PresetParam('_major'),
			'major2_color'=> $this->helix3->PresetParam('_major2'),
			'preloader_bg' => $preloader_bg,
            'preloader_tx' => $preloader_tx,
            ))
        ->addLess('master', 'template')
        ->addLess('presets',  'presets/'.$this->helix3->Preset())
    	->addJS('jquery.countdown.min.js');
		
		//Before Head
        if($before_head = $this->helix3->getParam('before_head')) {
            echo $before_head . "\n";
        }
    ?>
</head>
<body>
	<div class="csoon-header">
		<div class="container">
			<?php if($this->countModules('csoon-header')) { ?>
				<div class="sp-position-logo">				
					<jdoc:include type="modules" name="csoon-header" style="sp_xhtml" />
				</div>
			<?php } ?>
			
		</div>
	</div>
	<div class="sp-comingsoon-wrap">
		<div class="container">
			<div class="text-center">
				<div id="sp-comingsoon">
					
					<?php
					$sitename = JFactory::getApplication()->get('sitename');
					$html  = '';
					$html .= '<a class="logo" href="' . JURI::base(true) . '/">';
					if( $this->helix3->getParam('logo_type') == 'image' ) {
						if( $this->helix3->getParam('csoon_logo') ) {
							$html .= '<h1>';
							$html .= '<img class="sp-default-logo" src="' . $this->helix3->getParam('csoon_logo') . '" alt="'. $sitename .'">';
							$html .= '</h1>';
						} else {
							$html .= '<h1>';
							$html .= '<img class="sp-default-logo" src="' . $this->helix3->getTemplateUri() . '/images/presets/' . $this->helix3->Preset() . '/logo_cs.png" alt="'. $sitename .'">';
							$html .= '</h1>';
						}
						
					} else {
						if( $this->helix3->getParam('logo_text') ) {
							$html .= '<h1>' . $this->helix3->getParam('logo_text') . '</h1>';
						} else {
							$html .= '<h1>' . $sitename . '</h1>';
						}

						if( $this->helix3->getParam('logo_slogan') ) {
							$html .= '<p class="logo-slogan">' . $this->helix3->getParam('logo_slogan') . '</p>';
						}
					}
					$html .= '</a>';
					
					echo $html;
					?>
					
					<?php if( $comingsoon_title ) { ?>
						<h2 class="sp-comingsoon-title">
							<?php echo $comingsoon_title; ?>
						</h2>
					<?php } ?>

					<div id="sp-comingsoon-countdown" class="sp-comingsoon-countdown">
						
					</div>

					<?php if($this->countModules('comingsoon')) { ?>
					<div class="sp-position-comingsoon">
						<jdoc:include type="modules" name="comingsoon" style="sp_xhtml" />
					</div>
					<?php } ?>
					<?php if( $this->params->get('comingsoon_content') ) { ?>
						<div class="sp-comingsoon-content">
							<?php echo $this->params->get('comingsoon_content'); ?>
						</div>
					<?php } ?>
					<div class="form-login-wrapper">
						<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login" class="form-inline">
							<div class="form-group" id="form-login-username">
								<input name="username" id="username" type="text" class="form-control" placeholder="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" size="18" />
							</div>
							
							<div class="form-group" id="form-login-password">
								<input type="password" name="password" class="form-control" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" id="passwd" />
							</div>
							<?php if (count($twofactormethods) > 1) : ?>
							<div class="form-group" id="form-login-secretkey">
								<input type="text" name="secretkey" class="form-control" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" id="secretkey" />
							</div>
							<?php endif; ?>
							<div class="form-group" id="submit-buton">
								<input type="submit" name="Submit" class="btn btn-success login" value="<?php echo JText::_('JLOGIN'); ?>" />
							</div>

							<input type="hidden" name="option" value="com_users" />
							<input type="hidden" name="task" value="user.login" />
							<input type="hidden" name="return" value="<?php echo base64_encode(JUri::base()); ?>" />
							<?php echo JHtml::_('form.token'); ?>
						</form>
					</div>
				</div>
			</div>
			<?php
	//Social Icons
	$facebook 	= $this->params->get('facebook');
	$twitter  	= $this->params->get('twitter');
	$googleplus = $this->params->get('googleplus');
	$pinterest 	= $this->params->get('pinterest');
	$youtube 	= $this->params->get('youtube');
	$linkedin 	= $this->params->get('linkedin');
	$dribbble 	= $this->params->get('dribbble');
	$behance 	= $this->params->get('behance');
	$skype 		= $this->params->get('skype');
	$flickr 	= $this->params->get('flickr');
	$vk 		= $this->params->get('vk');

	if( $this->params->get('show_social_icons') && ( $facebook || $twitter || $googleplus || $pinterest || $youtube || $linkedin || $dribbble || $behance || $skype || $flickr || $vk ) ) {
		$html  = '<div class="csoon-social text-center"><div class="container"><ul class="social-icons">';

		if( $facebook ) {
			$html .= '<li><a target="_blank" class="jutooltip" title="Facebook" href="'. $facebook .'"><i class="fa fa-facebook"></i></a></li>';
		}
		if( $twitter ) {
			$html .= '<li><a target="_blank" class="jutooltip" title="Twitter" href="'. $twitter .'"><i class="fa fa-twitter"></i></a></li>';
		}
		if( $googleplus ) {
			$html .= '<li><a target="_blank" class="jutooltip" title="Google Plus" href="'. $googleplus .'"><i class="fa fa-google-plus"></i></a></li>';
		}
		if( $pinterest ) {
			$html .= '<li><a target="_blank" class="jutooltip" title="Pinterest" href="'. $pinterest .'"><i class="fa fa-pinterest"></i></a></li>';
		}
		if( $youtube ) {
			$html .= '<li><a target="_blank" class="jutooltip" title="Youtube" href="'. $youtube .'"><i class="fa fa-youtube"></i></a></li>';
		}
		if( $linkedin ) {
			$html .= '<li><a target="_blank" class="jutooltip" title="Linkedin" href="'. $linkedin .'"><i class="fa fa-linkedin"></i></a></li>';
		}
		if( $dribbble ) {
			$html .= '<li><a target="_blank" class="jutooltip" title="Dribbble" href="'. $dribbble .'"><i class="fa fa-dribbble"></i></a></li>';
		}
		if( $behance ) {
			$html .= '<li><a target="_blank" class="jutooltip" title="Behance" href="'. $behance .'"><i class="fa fa-behance"></i></a></li>';
		}
		if( $flickr ) {
			$html .= '<li><a target="_blank" class="jutooltip" title="Flickr" href="'. $flickr .'"><i class="fa fa-flickr"></i></a></li>';
		}
		if( $vk ) {
			$html .= '<li><a target="_blank" class="jutooltip" title="VKontakte" href="'. $vk .'"><i class="fa fa-vk"></i></a></li>';
		}
		if( $skype ) {
			$html .= '<li><a class="jutooltip" title="Skype" href="skype:'. $skype .'?chat"><i class="fa fa-skype"></i></a></li>';
		}

		$html .= '</ul></div></div>';

		echo $html;
	}

	?>
		</div>
	</div>



	
<div class="csoon-footer">
	<div class="container">
		<?php if($this->countModules('csoon-footer')) { ?>
			<div class="sp-position-footer text-center">				
				<jdoc:include type="modules" name="csoon-footer" style="sp_xhtml" />
			</div>
		<?php } ?>
	</div>
</div>
<script type="text/javascript">

	jQuery(function($) {
		$('#sp-comingsoon-countdown').countdown('<?php echo trim($comingsoon_date[2]); ?>/<?php echo trim($comingsoon_date[1]); ?>/<?php echo trim($comingsoon_date[0]); ?>', function(event) {
			$(this).html(event.strftime('<div class="days"><span class="number">%-D</span><span class="string">%!D:<?php echo JText::_("HELIX_DAY"); ?>,<?php echo JText::_("HELIX_DAYS"); ?>;</span></div><div class="hours"><span class="number">%H</span><span class="string">%!H:<?php echo JText::_("HELIX_HOUR"); ?>,<?php echo JText::_("HELIX_HOURS"); ?>;</span></div><div class="minutes"><span class="number">%M</span><span class="string">%!M:<?php echo JText::_("HELIX_MINUTE"); ?>,<?php echo JText::_("HELIX_MINUTES"); ?>;</span></div><div class="seconds"><span class="number">%S</span><span class="string">%!S:<?php echo JText::_("HELIX_SECOND"); ?>,<?php echo JText::_("HELIX_SECONDS"); ?>;</span></div>'));
		});
	});

</script>

</body>
</html>