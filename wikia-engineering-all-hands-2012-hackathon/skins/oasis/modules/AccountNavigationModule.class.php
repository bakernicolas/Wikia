<?php
/**
 * Renders anon / user menu at top right corner of the page
 *
 * @author Maciej Brencz
 */

class AccountNavigationModule extends WikiaController {

	// This one really is a local class variable
	var $personal_urls;
	
	/**
	 * Render personal URLs item as HTML link
	 */
	private function renderPersonalUrl($id) {
		wfProfileIn(__METHOD__);
		$personalUrl = $this->personal_urls[$id];

		$attributes = array(
			'data-id' => $id,
			'href' => $personalUrl['href']
		);

		if( in_array( $id, array( 'login', 'register' ) ) ) {
			$attributes['rel'] = 'nofollow';
		}

		// add class attribute
		if (isset($personalUrl['class'])){
			$attributes['class'] = $personalUrl['class'];
		}

		// add accesskey attribute
		switch ($id) {
			case 'mytalk':
				$attributes['accesskey'] = 'n';
				break;
			case 'login':
				$attributes['accesskey'] = 'o';
				break;
		}
		
		$ret = Xml::element('a', $attributes, $personalUrl['text']);
		
		wfProfileOut(__METHOD__);
		return $ret;
	}

	/**
	 * Modify personal URLs list
	 */
	private function setupPersonalUrls() {
		global $wgUser, $wgComboAjaxLogin;
		
		// Import the starting set of urls from the skin template
		$this->personal_urls = $this->app->getSkinTemplateObj()->data['personal_urls'];
		if ($wgUser->isAnon()) {
			// add login and register links for anons
			$skin = $wgUser->getSkin();

			// where to redirect after login
			$returnto = wfGetReturntoParam();

			if(empty($wgComboAjaxLogin)) {
				$signUpHref = Skin::makeSpecialUrl('UserLogin', $returnto);
			} else {
				$signUpHref = Skin::makeSpecialUrl('Signup', $returnto);
			}
			$this->personal_urls['login'] = array(
				'text' => wfMsg('login'),
				'href' => $signUpHref . "&type=login",
				'class' => 'ajaxLogin'
			);

			$this->personal_urls['register'] = array(
				'text' => wfMsg('oasis-signup'),
				'href' => $signUpHref . "&type=signup",
				'class' => 'ajaxRegister'
			);
		}
		else {
			// use Mypage message for userpage entry
			$this->personal_urls['userpage']['text'] = wfMsg('mypage');
		}
	}

	public function executeIndex() {
		wfProfileIn(__METHOD__);

		global $wgUser, $wgEnableUserLoginExt;

		$this->setupPersonalUrls();

		$this->itemsBefore = array();
		$this->isAnon = $wgUser->isAnon();
		$this->username = $wgUser->getName();

		if ($this->isAnon) {
			// facebook connect
			if (!empty($this->personal_urls['fbconnect']['html'])) {
				$this->itemsBefore = array($this->personal_urls['fbconnect']['html']);
			}

			// render Login and Register links
			$this->loginLink = $this->renderPersonalUrl('login');
			$this->registerLink = $this->renderPersonalUrl('register');
			$this->loginDropdown = '';
			if(!empty($wgEnableUserLoginExt)) {
				$this->loginDropdown = (string)F::app()->sendRequest( 'UserLoginSpecial', 'dropdown', array('param' => 'paramvalue' ));
			}
		}
		else {
			// piggyback
			if ( $wgUser->isAllowed( 'piggyback' ) ) {
				global $wgTitle, $wgRequest;
				if ( NS_USER == $wgTitle->getNamespace() && $wgUser->getName() != $wgTitle->getDBkey() ) {
					$this->piggyback = true;
					$this->piggybackUrl = Title::newFromText( 'Piggyback', NS_SPECIAL )->getLocalUrl();
					$this->piggybackTargetUserAvatar = AvatarService::renderAvatar( $wgTitle->getDBkey(), 20 );
					$this->piggybackDropdown = <<<EOT
<ul class="subnav"><li>
<form class="WikiaForm" name="" method="post" action="{$this->piggybackUrl}?action=submitlogin">
	<fieldset>
	<input type="hidden" value="{$wgTitle->getDBkey()}" name="wpOtherName">
	<input type="hidden" value="{$wgUser->getName()}" name="wpName">
	<div class="input-group required" style="color: black;"><label>Password</label><input type="password" value="" name="wpPassword"></div>
	</fieldset>
	<div class="submits"><input type="submit" name="wpLoginattempt" id="wpLoginattempt" value="Log in"></div>
	<br />&nbsp;
</form>
</li></ul>
EOT;
				}
			}

			// render user avatar and link to his user page
			$this->profileLink = AvatarService::getUrl($this->username);
			$this->profileAvatar = AvatarService::renderAvatar($this->username, 20);

			// dropdown items
			$possibleItems = array('mytalk', 'following', 'preferences');
			$dropdownItems = array();

			// Allow hooks to modify the dropdown items.
			$this->wf->RunHooks( 'AccountNavigationModuleAfterDropdownItems', array(&$possibleItems, &$this->personal_urls) );

			foreach($possibleItems as $item) {
				if (isset($this->personal_urls[$item])) {
					$dropdownItems[] = $this->renderPersonalUrl($item);
				}
			}

			// link to Help:Content (never render as redlink)
			$helpLang = array_key_exists( $this->wg->LanguageCode, $this->wg->AvailableHelpLang ) ? $this->wg->LanguageCode : 'en';
			$dropdownItems[] = Wikia::link(
				Title::newFromText( wfMsgExt( 'helppage', array( 'parsemag', 'language' => $helpLang ) ) ),
				wfMsg('help'),
				array('title' => '', 'data-id' => 'help'),
				'',
				array('known')
			);

			// logout link
			$dropdownItems[] = $this->renderPersonalUrl('logout');
			$this->dropdown = $dropdownItems;
		}

		wfProfileOut(__METHOD__);
	}
}
