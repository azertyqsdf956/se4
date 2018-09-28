/**

 * Page qui teste les differents services
 * @Version $Id: gest_messages.js 3002 2008-05-30 12:58:43Z keyser $
 *
   * @Projet LCS / SambaEdu
   * @Auteurs Equipe Sambaedu
   * @Licence Distribue sous la licence GPLlancement des scripts bash par la technologie asynchrone Ajax.


 * Ce script affecte les messages par defaut au chargement de la page
 * Le contenu des info-bulles sont modifiees ensuite  dans le script tests.js en fonction du resultat du test
 */

/**
 *
 * @Repertoire: /tests/js/ file: gest_messages.js
 */

// Affecte les messages par defaut des info-bulles.
var duration = 5000;

function init_default_msg() {

	$('help_maj_se3').onmouseover = function() {

		UnTip();
		Tip(msg_maj_info, STICKY, true, CLICKCLOSE, true, WIDTH, 250, SHADOW,
				true, DURATION, duration);
		this.onmousout = function() {
			unTip();
		}

	}
	$('help_clonage_se3').onmouseover = function() {
		UnTip();
		Tip(msg_clonage_info, STICKY, true, CLICKCLOSE, true, WIDTH, 250,
				SHADOW, true, DURATION, duration);

	}
	$('help_gateway_se3').onmouseover = function() {
		UnTip();
		Tip(msg_gateway_info, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}

	}
	$('help_net_se3').onmouseover = function() {
		UnTip();
		Tip(msg_net_info, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}

	}
	$('help_dns_se3').onmouseover = function() {
		UnTip();
		Tip(msg_dns_info, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}

	}
	$('help_dns2_se3').onmouseover = function() {
		UnTip();
		Tip(msg_dns2_info, STICKY, true, WIDTH, 250, SHADOW, true, DURATION,
				duration);

	}

	$('help_web_se3').onmouseover = function() {
		UnTip();
		Tip(msg_web_info, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}

	}

	$('check_mail').onmouseover = function() {
		UnTip();
		TagToTip('mail_menu', STICKY, true, CLICKCLOSE, true, DURATION,
				duration);

	}

	$('help_mail_se3').onmouseover = function() {
		UnTip();
		Tip(msg_mail_info, STICKY, true, CLICKCLOSE, true, WIDTH, 250, SHADOW,
				true, DURATION, duration);

	}

	$('help_samba_se3').onmouseover = function() {
		UnTip();
		Tip(msg_samba_info, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}

	}

	$('help_sid_se3').onmouseover = function() {
		UnTip();
		Tip(msg_sid_info, WIDTH, 250);
		this.onmouseout = function() {
			UnTip();
		}

	}

	$('help_mysql_se3').onmouseover = function() {
		UnTip();
		Tip(msg_mysql_info, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}

	}

	$('help_ondul_se3').onmouseover = function() {
		UnTip();
		Tip(msg_ondul_ko_info, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}
	}
	if ($('help_dhcp_se3')) {

		$('help_dhcp_se3').onmouseover = function() {
			UnTip();
			Tip(msg_dhcp_info, STICKY, true, CLICKCLOSE, true, WIDTH, 250,
					SHADOW, true, DURATION, duration);
		}

	}

	if ($('help_time_se3')) {

		$('help_time_se3').onmouseover = function() {
			UnTip();
			Tip(msg_time_info, STICKY, true, CLICKCLOSE, true, WIDTH, 250,
					SHADOW, true, DURATION, duration);
		}

	}

	$('help_secu_se3').onmouseover = function() {
		UnTip();
		Tip(msg_secu_info, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}

	}

	$('help_client_se3').onmouseover = function() {
		UnTip();
		Tip(msg_client_info, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}
	}

	$('check_maj').onmouseover = function() {
		UnTip();
		Tip(msg_maj_nocx, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}
	}
	$('check_clonage').onmouseover = function() {
		UnTip();
		Tip(msg_clonage_nocx, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}
	}
	$('check_web').onmouseover = function() {
		UnTip();
		Tip(msg_web_nocx, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}
	}
	$('check_secu').onmouseover = function() {
		UnTip();
		Tip(msg_secu_nocx, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}
	}
//	$('check_ntp').onmouseover = function() {
//		UnTip();
//		Tip(msg_ntp_nocx, WIDTH, 250, SHADOW, true, DURATION, duration);
//		this.onmouseout = function() {
//			UnTip();
//		}
//	}
	$('check_dns').onmouseover = function() {
		UnTip();
		Tip(msg_dns_nocx, WIDTH, 250, SHADOW, true, DURATION, duration);
		this.onmouseout = function() {
			UnTip();
		}
	}

}