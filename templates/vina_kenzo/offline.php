<?php
/**
 * @package Helix3 Framework
 * Template Name - Shaper Helix - iii
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2015 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

$doc = JFactory::getDocument();
$app = JFactory::getApplication();

require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

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

$twofactormethods = UsersHelper::getTwoFactorMethods();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    if($favicon = $this->params->get('favicon')) {
        $doc->addFavicon( JURI::base(true) . '/' .  $favicon);
    } else {
        $doc->addFavicon( $this->baseurl . '/templates/'. $this->template .'/images/favicon.ico' );
    }
    ?>
	<?php
	$preloader_bg  = ($this->helix3->getParam('preloader_bg')) ? $this->helix3->getParam('preloader_bg') : '#f5f5f5';
    $preloader_tx  = ($this->helix3->getParam('preloader_tx')) ? $this->helix3->getParam('preloader_tx') : '#f5f5f5';
    $this->helix3->addCSS('bootstrap.min.css, font-awesome.min.css')
        ->lessInit()->setLessVariables(array(
            'preset'=>$this->helix3->Preset(),
            'bg_color'=> $this->helix3->PresetParam('_bg'),
            'text_color'=> $this->helix3->PresetParam('_text'),
            'major_color'=> $this->helix3->PresetParam('_major'),
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
    <jdoc:include type="head" />
   	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/bootstrap.min.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/template.css" type="text/css" />
<body class="offsite">
	<div class="site-offline">
		<div class="container">

			<div class="offline-inner">
				<jdoc:include type="message" />
				<div class="text-center">
					<div id="sp-comingsoon">

						<?php if($this->countModules('comingsoon')) { ?>
						<div class="sp-position-comingsoon">
							<jdoc:include type="modules" name="comingsoon" style="sp_xhtml" />
						</div>
						<?php } ?>
						<?php if ($app->get('offline_image') && file_exists($app->get('offline_image'))) : ?>
							<img src="<?php echo $app->get('offline_image'); ?>" alt="<?php echo htmlspecialchars($app->get('sitename')); ?>" />
						<?php else : ?>
						<h1>
							<?php echo htmlspecialchars($app->get('sitename')); ?>
						</h1>
						<?php endif; ?>
						<?php if ($app->get('display_offline_message', 1) == 1 && str_replace(' ', '', $app->get('offline_message')) != '') : ?>
									<p>
										<?php echo $app->get('offline_message'); ?>
									</p>
								<?php elseif ($app->get('display_offline_message', 1) == 2 && str_replace(' ', '', JText::_('JOFFLINE_MESSAGE')) != '') : ?>
									<p>
										<?php echo JText::_('JOFFLINE_MESSAGE'); ?>
									</p>
								<?php endif; ?>
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
</body>
</html>
