/* ========================================================================
 * DOM-based Routing
 * Based on http://goo.gl/EUTi53 by Paul Irish
 *
 * Only fires on body classes that match. If a body class contains a dash,
 * replace the dash with an underscore when adding it to the object below.
 *
 * .noConflict()
 * The routing is enclosed within an anonymous function so that you can
 * always reference jQuery with $, even when in .noConflict() mode.
 * ======================================================================== */
(function($) {

  // Domains a cookie has to be expired on, besides host-only. Mirrors
  // Moove_GDPR_Controller::moove_gdpr_get_cookie_domains() so both removal methods agree.
  function gdpr_get_cookie_domains( name, host ) {
    var domains   = [ host, host.replace( /^www\./, '' ) ];
    var d_domains = typeof moove_frontend_gdpr_scripts.parent_domain_cookies !== 'undefined' ? moove_frontend_gdpr_scripts.parent_domain_cookies : [];
    for ( var i = 0; i < d_domains.length; i++ ) {
      if ( d_domains[i] && name.indexOf( d_domains[i] ) !== -1 ) {
        // gtag and friends write on the registrable domain (blog.example.com -> example.com),
        // so walk up every level. Browsers ignore the public-suffix ones.
        var labels = host.split('.');
        while ( labels.length > 1 ) {
          domains.push( labels.join('.') );
          labels.shift();
        }
        break;
      }
    }
    return domains;
  }

  /**
   * Expires the cookies Google's tags write once consent is granted.
   *
   * With geo location, the Consent Mode default can grant consent before the
   * visitor's location is known (see gdpr_consent_regions() in
   * class-moove-gdpr-content.php). When the banner turns out to be needed after
   * all, the denied update stops Google's tags writing cookies but leaves the ones
   * they wrote in the meantime. The visitor has not consented to anything, so those
   * go. The other categories are still held back at that point and cannot have set
   * anything, so only Google's cookies are removed.
   */
  function gdpr_expire_google_cookies() {
    try {
      var host    = window.location.hostname.toLowerCase();
      var cookies = document.cookie.split(';');
      for ( var i = 0; i < cookies.length; i++ ) {
        var name = cookies[i].split('=')[0].trim();
        // _ga, _ga_<id>, _gac_<id>, _gid, _gat, _gat_<id>, _gcl_au/_aw/_dc/..., legacy __utm*.
        if ( ! /^(_ga(_.*)?|_gac_.*|_gid|_gat(_.*)?|_gcl_.*|__utm[abcztv])$/.test( name ) ) {
          continue;
        }
        var expired = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
        var domains = gdpr_get_cookie_domains( name, host );
        document.cookie = expired;
        for ( var j = 0; j < domains.length; j++ ) {
          document.cookie = expired + ';domain=.' + domains[j];
        }
      }
    } catch ( e ) {
      // document.cookie throws in a sandboxed iframe: there is nothing to remove.
    }
  }

  // Use this variable to set up the common and page specific functions. If you
  // rename this variable, you will also need to rename the namespace below.
  var GDPR_FE = {
    // All pages
    'common': {
      init: function() {
        'use strict';
        var cookie_expiration = 365;
        var gdpr_cookies_loaded = [];
        var icons_loaded = false;
        var consent_log_all = false;
        if ( typeof moove_frontend_gdpr_scripts.cookie_expiration !== 'undefined' ) {
          cookie_expiration = moove_frontend_gdpr_scripts.cookie_expiration;
        }

        $(document).on('click','#moove_gdpr_cookie_modal .moove-gdpr-modal-content.moove_gdpr_modal_theme_v1 .main-modal-content .moove-gdpr-tab-main:not(#privacy_overview) .tab-title', function(e){
          if( window.innerWidth < 768 ) {
            var expand   = ! $(this).closest('.moove-gdpr-tab-main').find('.moove-gdpr-tab-main-content').is(':visible');
            var duration = typeof window.matchMedia === 'function' && window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 300;
            $(this).find('.gdpr-tab-title-text[role="button"]').attr( 'aria-expanded', expand ? 'true' : 'false' );
            if ( expand ) {
              $(this).closest('.moove-gdpr-tab-main').find('.moove-gdpr-tab-main-content').slideDown( duration );
            } else {
              $(this).closest('.moove-gdpr-tab-main').find('.moove-gdpr-tab-main-content').slideUp( duration );
            }
          }
        });

        function gdpr_validate_url( string ) {
          var url;
          try {
            url = new URL( string );
          } catch (_) {
            return false;
          }
          return url.protocol === "http:" || url.protocol === "https:";
        }

        $(document).on('click tap','#moove_gdpr_cookie_info_bar .moove-gdpr-infobar-reject-btn, [href*="#gdpr-reject-cookies"], .moove-gdpr-modal-reject-all',function(e){
          e.preventDefault();
          gdpr_delete_all_cookies();
          gdpr_ajax_delete_cookies();

          if ( $('#moove_gdpr_cookie_info_bar').length > 0 ) {
            $('#moove_gdpr_cookie_info_bar').addClass('moove-gdpr-info-bar-hidden');
            $('body').removeClass('gdpr-infobar-visible');
            $('#moove_gdpr_cookie_info_bar').hide();
            $('#moove_gdpr_save_popup_settings_button').show();
          }

          $('.gdpr_lightbox .gdpr_lightbox-close').trigger('click');
          $(document).moove_gdpr_lightbox_close();

          if ( ( typeof moove_frontend_gdpr_scripts.gdpr_scor !== 'undefined' ) && moove_frontend_gdpr_scripts.gdpr_scor === 'false' )  {
          } else {
            moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: '1', thirdparty: '0', advanced: '0', performance: '0',  preference: '0'}),cookie_expiration);
            setTimeout(function(){
              moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: '1', thirdparty: '0', advanced: '0', performance: '0',  preference: '0'}),cookie_expiration);
            }, 500);
          }
          
          moove_gdpr_check_reload( 'reject-btn' );
        });
        // The control that opened the settings modal, so focus can be handed back on close.
        var gdpr_modal_return_focus = null;

        function gdpr_cc_log( log ) {
          try {
            var urlParams = new URLSearchParams(window.location.search);
            if ( urlParams.has('gdpr_dbg') ) {
              console.warn( log );
            }
          } catch (e) {
            console.warn(e);
          }
        }

        /**
         * postscribe() with an error handler that does not throw.
         *
         * postscribe's default handler throws, before it moves on to the next script. One
         * script that fails to load (an ad blocker, a network error) or throws therefore
         * stopped every script queued after it, in every category - the Consent Mode
         * update included. The failure is reported instead and the rest still run.
         */
        function gdpr_postscribe( el, html ) {
          postscribe( el, html, {
            error: function( e ) {
              console.warn( 'GDPR Cookie Compliance - script failed: ' + ( e && e.msg ? e.msg : e ) );
            }
          } );
        }

        $.fn.moove_gdpr_read_cookies = function(options){
          var cookies = moove_gdpr_read_cookie('moove_gdpr_popup');
          var cookie_values = {};

          cookie_values['strict'] = '0';
          cookie_values['thirdparty'] = '0';
          cookie_values['advanced'] = '0';
          cookie_values['performance'] = '0';
          cookie_values['preference'] = '0';
          if ( cookies ) {
            cookies = JSON.parse( cookies );
            cookie_values['strict'] = parseInt(cookies.strict);
            cookie_values['thirdparty'] = parseInt(cookies.thirdparty);
            cookie_values['advanced'] = parseInt(cookies.advanced);
            cookie_values['performance'] = typeof cookies.performance !== 'undefined' ? parseInt(cookies.performance) : 0;
            cookie_values['preference'] = typeof cookies.preference !== 'undefined' ? parseInt(cookies.preference) : 0;
          }
          return cookie_values;

        }

        function gdpr_ajax_php_delete_cookies() {
          var ajax_cookie_removal = typeof moove_frontend_gdpr_scripts.ajax_cookie_removal !== 'undefined' ? moove_frontend_gdpr_scripts.ajax_cookie_removal : 'false';
          var security = typeof moove_frontend_gdpr_scripts.gdpr_nonce !== 'undefined' ? moove_frontend_gdpr_scripts.gdpr_nonce : 'false';
          if ( ajax_cookie_removal === 'true' ) {
            if ( 'function' === typeof navigator.sendBeacon ) {
              var log_data = new FormData();
              log_data.append('action', 'moove_gdpr_remove_php_cookies');
              log_data.append('security', security );
              log_data.append('type', 'navigatorBeacon' );
              navigator.sendBeacon( moove_frontend_gdpr_scripts.ajaxurl, log_data );
              gdpr_cc_log('dbg - cookies removed navigatorBeacon')
            } else {              
              $.post(
                moove_frontend_gdpr_scripts.ajaxurl,
                {
                  action: "moove_gdpr_remove_php_cookies",
                  security: security,
                  type: 'ajax_b1',
                },
                function( msg ) {
                  gdpr_cc_log('dbg - cookies removed');                
                }
              );
            }
          }
        }

        function gdpr_ajax_delete_cookies() {
          gdpr_ajax_php_delete_cookies();
          var wp_lang = typeof moove_frontend_gdpr_scripts.wp_lang !== 'undefined' ? moove_frontend_gdpr_scripts.wp_lang : '';
          var ajax_cookie_removal = typeof moove_frontend_gdpr_scripts.ajax_cookie_removal !== 'undefined' ? moove_frontend_gdpr_scripts.ajax_cookie_removal : 'false';
          if ( ajax_cookie_removal === 'true' ) {
            $.post(
              moove_frontend_gdpr_scripts.ajaxurl,
              {
                action: "moove_gdpr_get_scripts",
                strict: 0,
                thirdparty: 0,
                advanced: 0,
                performance: 0,
                preference: 0,
                wp_lang: wp_lang,
              },
              function( msg ) {
                var cookie_values = {};

                cookie_values['strict'] = 1;
                cookie_values['thirdparty'] = 0;
                cookie_values['advanced'] = 0;
                cookie_values['performance'] = 0;
                cookie_values['preference'] = 0;
                gdpr_delete_all_cookies();
                
                gdpr_save_analytics( 'script_inject', cookie_values );
                moove_gdpr_change_switchers( cookie_values );
              }
            );
          } else {
            var cookie_values = {};

            cookie_values['strict'] = 1;
            cookie_values['thirdparty'] = 0;
            cookie_values['advanced'] = 0;
            cookie_values['performance'] = 0;
            cookie_values['preference'] = 0;
            gdpr_delete_all_cookies();

            // Mirror the AJAX branch above: the rejection still has to be recorded against
            // the WP Consent API, otherwise consent-aware plugins are left with no value.
            moove_gdpr_change_switchers( cookie_values );
          }
        }

        function gdpr_save_analytics( $options, $extras ) {
          if ( typeof jQuery.fn.gdpr_cookie_compliance_analytics === 'function' ) {
            jQuery().gdpr_cookie_compliance_analytics( $options, $extras );
          }
        }

        /**
         * Push a consent value to the WP Consent API.
         *
         * wp_set_consent() lives in the WP Consent API's own script, which is a separate
         * file we only depend on, not control. If an optimisation plugin defers, delays or
         * combines it, the global can still be missing by the time we run - bail out quietly
         * rather than throwing a ReferenceError that aborts the whole caller.
         */
        function gdpr_wp_set_consent( category, value ) {
          if ( typeof moove_frontend_gdpr_scripts.wp_consent_api === 'undefined' || 'true' !== moove_frontend_gdpr_scripts.wp_consent_api ) {
            return;
          }

          if ( typeof wp_set_consent !== 'function' ) {
            gdpr_cc_log( 'wp_set_consent() unavailable - the WP Consent API script has not loaded' );
            return;
          }

          gdpr_cc_log( 'wp_set_consent: ' + category + ' - ' + value );
          wp_set_consent( category, value );
        }

        /**
         * The cookie categories the analytics and advertising consent follow on this site:
         * 'thirdparty' and 'advanced', unless the site has switched one of the two off and
         * the other covers both. See gdpr_consent_signal_categories() in
         * class-moove-gdpr-content.php.
         */
        function gdpr_consent_signal_cats() {
          return typeof moove_frontend_gdpr_scripts.consent_signals !== 'undefined' ? moove_frontend_gdpr_scripts.consent_signals : { analytics: 'thirdparty', ads: 'advanced' };
        }

        function gdpr_save_consent_log( value ) {
          if ( typeof jQuery.fn.gdpr_cookie_compliance_consent_log === 'function' ) {
            jQuery().gdpr_cookie_compliance_consent_log( value );
          }
        }

        function m_g_read_cookies() {
          var cookies = moove_gdpr_read_cookie('moove_gdpr_popup');
          var cookie_values = {};

          cookie_values['strict'] = '0';
          cookie_values['thirdparty'] = '0';
          cookie_values['advanced'] = '0';
          cookie_values['performance'] = '0';
          cookie_values['preference'] = '0';

          if ( cookies ) {
            cookies = JSON.parse( cookies );
            cookie_values['strict'] = cookies.strict;
            cookie_values['thirdparty'] = cookies.thirdparty;
            cookie_values['advanced'] = cookies.advanced;
            cookie_values['performance'] = typeof cookies.performance !== 'undefined' ? cookies.performance : 0;
            cookie_values['preference'] = typeof cookies.preference !== 'undefined' ? cookies.preference : 0;
            moove_gdpr_change_switchers( cookie_values );
            gdpr_save_analytics( 'script_inject', cookies );
          }

          if ( typeof moove_frontend_gdpr_scripts.ifbc !== 'undefined' ) {
            if ( moove_frontend_gdpr_scripts.ifbc === 'strict' && cookies && parseInt( cookies.strict ) === 1 ) {
              gdpr_remove_iframe_restrictions();
            }

            if ( moove_frontend_gdpr_scripts.ifbc === 'thirdparty' && cookies && parseInt( cookies.thirdparty ) === 1 ) {
              gdpr_remove_iframe_restrictions();
            }

            if ( moove_frontend_gdpr_scripts.ifbc === 'advanced' && cookies && parseInt( cookies.advanced ) === 1 ) {
              gdpr_remove_iframe_restrictions();
            }

            if ( moove_frontend_gdpr_scripts.ifbc === 'performance' && cookies && typeof cookies.performance !== 'undefined' && parseInt( cookies.performance ) === 1 ) {
              gdpr_remove_iframe_restrictions();
            }

            if ( moove_frontend_gdpr_scripts.ifbc === 'preference' && cookies && typeof cookies.preference !== 'undefined' && parseInt( cookies.preference ) === 1 ) {
              gdpr_remove_iframe_restrictions();
            }

          } else {
            if ( moove_frontend_gdpr_scripts.strict_init !== '1' ) {
              gdpr_remove_iframe_restrictions();
            }
          }
          return cookie_values;

        }

        function gdpr_remove_iframe_restrictions() {
          $(document).find("iframe[data-gdpr-iframesrc]").each(function(){
            $(this).attr('src',$(this).attr('data-gdpr-iframesrc'));
          });
        }

        var initial_cookies = m_g_read_cookies();
        var injected_scripts = false;
        var is_created = false;

        var modal_instance = '';
        var is_gdpr_lightbox = false;
        var consent_values = '';
        // JavaScript to be fired on all pages
        function moove_gdpr_save_cookies( $log ) {
          consent_log_all = true;
          gdpr_save_analytics( 'accept_all', '' );
          
          moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: '1', thirdparty: '1', advanced: '1', performance: '1', preference: '1'}),cookie_expiration);
          moove_gdpr_check_reload( 'enabled-all' );
          
        }   

        /**
         * GA4 measurement IDs (G-XXXXXXX) running in this page, whichever way they were
         * added: a GTM container, a Google tag (GT-) or a gtag.js snippet.
         */
        function gdpr_ga4_measurement_ids() {
          var ids = {};
          var add = function( id ) {
            if ( typeof id === 'string' && /^G-[A-Z0-9]+$/i.test( id ) ) {
              ids[ id.toUpperCase() ] = true;
            }
          };

          // Every Google tag registers its destinations here once it has loaded.
          if ( window.google_tag_manager && typeof window.google_tag_manager === 'object' ) {
            Object.keys( window.google_tag_manager ).forEach( add );
          }

          // gtag('config', 'G-...') calls, including any whose tag is still loading.
          if ( window.dataLayer && window.dataLayer.length ) {
            for ( var i = 0; i < window.dataLayer.length; i++ ) {
              if ( window.dataLayer[ i ] && window.dataLayer[ i ][0] === 'config' ) {
                add( window.dataLayer[ i ][1] );
              }
            }
          }

          // gtag.js loaded for a measurement ID, as GTM does for its GA4 tags.
          $(document).find('script[src*="googletagmanager.com/gtag/js"]').each(function(){
            if ( this.src && gdpr_validate_url( this.src ) ) {
              add( new URL( this.src ).searchParams.get('id') );
            }
          });

          return Object.keys( ids );
        }

        /**
         * Stops the Google tags already running in this page when consent is withdrawn.
         *
         * Withdrawal reloads the page, and the reloaded page no longer loads what was
         * withdrawn. Until then the tags in this page keep the consent they started with,
         * and can still send hits - including the one Google Analytics sends as the page
         * is left. A denied consent update alone does not stop them, as Google's tags
         * carry on with cookieless pings. So Google Analytics is switched off with
         * Google's opt-out flag, which stops its requests altogether, and the withdrawn
         * signals are updated to denied for the other Google tags (Ads).
         *
         * Only denied values are sent: the signals of the categories the visitor kept
         * are left as they are. The category -> signal mapping matches the Consent Mode
         * v2 snippets in class-moove-gdpr-content.php.
         *
         * @param {Array} withdrawn Cookie category keys the visitor has just withdrawn.
         */
        function gdpr_stop_withdrawn_google_tags( withdrawn ) {
          var signal_cats = gdpr_consent_signal_cats();
          var update      = {};

          try {
            if ( withdrawn.indexOf( signal_cats.analytics ) !== -1 ) {
              gdpr_ga4_measurement_ids().forEach(function( id ) {
                window['ga-disable-' + id] = true;
                gdpr_cc_log( 'ga-disable: ' + id );
              });
              update.analytics_storage = 'denied';
            }

            if ( withdrawn.indexOf( signal_cats.ads ) !== -1 ) {
              update.ad_storage         = 'denied';
              update.ad_user_data       = 'denied';
              update.ad_personalization = 'denied';
            }

            if ( withdrawn.indexOf( 'preference' ) !== -1 ) {
              update.personalization_storage = 'denied';
            }

            if ( withdrawn.indexOf( 'strict' ) !== -1 ) {
              update.functionality_storage = 'denied';
              update.security_storage      = 'denied';
            }

            // Consent commands must reach the dataLayer as an arguments object, the way
            // gtag() pushes them: GTM ignores the same values pushed as an array.
            if ( window.dataLayer && ! $.isEmptyObject( update ) ) {
              (function() {
                window.dataLayer.push( arguments );
              })( 'consent', 'update', update );
              gdpr_cc_log( 'consent update: ' + JSON.stringify( update ) );
            }
          } catch ( e ) {
            // The reload that follows must still happen.
            gdpr_cc_log( 'error in gdpr_stop_withdrawn_google_tags: ' + e );
          }
        }

        function moove_gdpr_check_reload( $log ) {
          var reload_page = false;
          var withdrawn   = [];
          // console.log($log);
          try {
            if ( typeof moove_frontend_gdpr_scripts.force_reload !== 'undefined' ) {
              if ( moove_frontend_gdpr_scripts.force_reload === 'true' ) {
                reload_page   = true;
              }
            }
          } catch(err) {
            // console.warn(err);
          }


          var current_cookies = m_g_read_cookies();

          var default_strict = moove_frontend_gdpr_scripts.enabled_default.strict;
          var default_trirdparty = moove_frontend_gdpr_scripts.enabled_default.third_party;
          var default_advanced = moove_frontend_gdpr_scripts.enabled_default.advanced;

          var default_performance = typeof moove_frontend_gdpr_scripts.enabled_default.performance !== 'undefined' ? moove_frontend_gdpr_scripts.enabled_default.performance : false ;
          var default_preference = typeof moove_frontend_gdpr_scripts.enabled_default.preference !== 'undefined' ? moove_frontend_gdpr_scripts.enabled_default.preference : false ;

          var is_created = false;

          if ( ( document.cookie.indexOf("moove_gdpr_popup") >= 0 ) || ( default_trirdparty == 1 || default_advanced == 1 || default_performance == 1 || default_preference == 1 || default_strict == 1 ) ) {
            var cookies = moove_gdpr_read_cookie('moove_gdpr_popup');

            if ( default_strict == 1 ) {
              initial_cookies.strict = 1;
            }

            if ( default_trirdparty == 1 ) {
              initial_cookies.strict = 1;      
              initial_cookies.thirdparty = default_trirdparty;
            }
            if ( default_advanced == 1 ) {
              initial_cookies.strict = 1;              
              initial_cookies.advanced = default_advanced;
            }

            if ( default_performance == 1 ) {
              initial_cookies.strict = 1;              
              initial_cookies.performance = default_performance;
            }

            if ( default_preference == 1 ) {
              initial_cookies.strict = 1;              
              initial_cookies.preference = default_preference;
            }

            if ( initial_cookies ) {
              if ( parseInt(current_cookies.strict) - parseInt(initial_cookies.strict) < 0 ) {
                reload_page = true;
                withdrawn.push( 'strict' );
              }
              if ( parseInt(current_cookies.thirdparty) - parseInt(initial_cookies.thirdparty) < 0 ) {
                reload_page = true;
                withdrawn.push( 'thirdparty' );
              }

              if ( parseInt(current_cookies.advanced) - parseInt(initial_cookies.advanced) < 0 ) {
                reload_page = true;
                withdrawn.push( 'advanced' );
              }

              if ( parseInt(current_cookies.performance) - parseInt(initial_cookies.performance) < 0 ) {
                reload_page = true;
                withdrawn.push( 'performance' );
              }

              if ( parseInt(current_cookies.preference) - parseInt(initial_cookies.preference) < 0 ) {
                reload_page = true;
                withdrawn.push( 'preference' );
              }
            }

          }

          if ( reload_page ) {
            if ( withdrawn.length > 0 ) {
              gdpr_stop_withdrawn_google_tags( withdrawn );
            }

            cookies = {
              "strict" : 0,
              "thirdparty" : 0,
              "advanced" : 0,
              "performance": 0,
              "preference": 0
            };
            gdpr_save_analytics( 'script_inject', cookies );
            
            if ( typeof moove_frontend_gdpr_scripts.scripts_defined !== 'undefined' ) {
              setTimeout(function(){
                location.reload(true);
              }, 800);
            } else {
              var _ga_script = $(document).find('script[src*="googletagmanager.com"]');

              if ( _ga_script.length > 0 ) {
                _ga_script.each(function(){
                  var _ga_src = $(this).attr('src');
                  if ( _ga_src && gdpr_validate_url( _ga_src ) ) {
                    var _ga_url_q = new URL(_ga_src);
                    var _ga_ID = _ga_url_q.searchParams.get("id");
                    if ( _ga_ID ) {
                      document.cookie = 'woocommerce_' + _ga_ID + '=true; expires=Thu, 31 Dec 1970 23:59:59 UTC; path=/';
                      window['ga-disable-' + _ga_ID] = true;
                    }
                    $(this).remove();
                  }
                });
              }
              var ajax_cookie_removal = typeof moove_frontend_gdpr_scripts.ajax_cookie_removal !== 'undefined' ? moove_frontend_gdpr_scripts.ajax_cookie_removal : 'true';
              var _security = typeof moove_frontend_gdpr_scripts.gdpr_nonce !== 'undefined' ? moove_frontend_gdpr_scripts.gdpr_nonce : 'false';

              if ( 'function' === typeof navigator.sendBeacon ) {
                if ( ajax_cookie_removal === 'true' ) {
                  var log_data = new FormData();
                  log_data.append('action', 'moove_gdpr_remove_php_cookies');
                  log_data.append('security', _security );
                  log_data.append('type', 'navigatorBeacon' );
                  navigator.sendBeacon( moove_frontend_gdpr_scripts.ajaxurl, log_data );
                  location.reload(true);
                } else {
                  location.reload(true);
                }
              } else {
                if ( ajax_cookie_removal === 'true' ) {
                  $.post(
                    moove_frontend_gdpr_scripts.ajaxurl,
                    {
                      action: "moove_gdpr_remove_php_cookies",
                      security: _security,
                      type: 'ajax_b2',
                    },
                    function( msg ) {
                      location.reload(true);
                    }
                  ).fail(function(){
                     location.reload(true);
                  });
                } else {
                  location.reload(true);
                }
              }
            }                      
          } else {
            var cookies_to_load = moove_gdpr_read_cookie('moove_gdpr_popup');
            gdpr_cc_log('dbg - inject - 4');
            moove_gdpr_inject_scripts_on_load(cookies_to_load);
            moove_gdpr_hide_infobar();
            $('#moove_gdpr_save_popup_settings_button').show();
          }          
        }

        function moove_gdpr_change_switchers( cookies ) {
          if ( cookies ) {
            gdpr_save_analytics( 'script_inject', cookies );
            if ( parseInt( cookies.strict ) === 1 ) {
              if ( ! $('#moove_gdpr_strict_cookies').is(':checked') ) {

                $('#moove_gdpr_strict_cookies').prop('checked', true).trigger('change'); // + 131022

                $('#third_party_cookies fieldset, #third_party_cookies .gdpr-cc-form-fieldset').removeClass('fl-disabled');
                $('#moove_gdpr_performance_cookies').prop('disabled',false);
                $('#third_party_cookies .moove-gdpr-strict-secondary-warning-message').slideUp();

                $('#advanced-cookies fieldset, #advanced-cookies .gdpr-cc-form-fieldset').removeClass('fl-disabled');
                $('#advanced-cookies .moove-gdpr-strict-secondary-warning-message').slideUp();
                $('#moove_gdpr_advanced_cookies').prop('disabled',false);

                $('#performance-cookies fieldset, #performance-cookies .gdpr-cc-form-fieldset').removeClass('fl-disabled');
                $('#performance-cookies .moove-gdpr-strict-secondary-warning-message').slideUp();
                $('#moove_gdpr_performance_cc_cookies').prop('disabled',false);

                $('#preference-cookies fieldset, #preference-cookies .gdpr-cc-form-fieldset').removeClass('fl-disabled');
                $('#preference-cookies .moove-gdpr-strict-secondary-warning-message').slideUp();
                $('#moove_gdpr_preference_cc_cookies').prop('disabled',false);
              }
              // WP Consent API
              gdpr_wp_set_consent( 'functional', 'allow' );
            } else {
              if ( $('#moove_gdpr_strict_cookies').is(':checked') ) {
                $('#moove_gdpr_strict_cookies').prop('checked',true).trigger('change'); // + 131022

                $('#third_party_cookies fieldset, #third_party_cookies .gdpr-cc-form-fieldset').addClass('fl-disabled').closest('.moove-gdpr-status-bar').removeClass('checkbox-selected');
                $('#moove_gdpr_performance_cookies').prop('disabled',true).prop('checked',false);
               
                $('#advanced-cookies fieldset, #advanced-cookies .gdpr-cc-form-fieldset').addClass('fl-disabled').closest('.moove-gdpr-status-bar').removeClass('checkbox-selected');
                $('#moove_gdpr_advanced_cookies').prop('disabled',true).prop('checked',false);

                $('#performance-cookies fieldset, #performance-cookies .gdpr-cc-form-fieldset').addClass('fl-disabled').closest('.moove-gdpr-status-bar').removeClass('checkbox-selected');
                $('#moove_gdpr_performance_cc_cookies').prop('disabled',true).prop('checked',false);

                $('#preference-cookies fieldset, #preference-cookies .gdpr-cc-form-fieldset').addClass('fl-disabled').closest('.moove-gdpr-status-bar').removeClass('checkbox-selected');
                $('#moove_gdpr_preference_cc_cookies').prop('disabled',true).prop('checked',false);

              }
              // WP Consent API
              gdpr_wp_set_consent( 'functional', 'deny' );
            }

            if ( parseInt( cookies.thirdparty ) === 1 ) {
              if ( ! $('#moove_gdpr_performance_cookies').is(':checked') ) {
                $('#moove_gdpr_performance_cookies').prop('checked', true).trigger('change'); // + 131022
              }
            } else {
              if ( $('#moove_gdpr_performance_cookies').is(':checked') ) {
                $('#moove_gdpr_performance_cookies').prop('checked', false).trigger('change'); // + 131022
              }
            }

            if ( parseInt( cookies.advanced ) === 1 ) {
              if ( ! $('#moove_gdpr_advanced_cookies').is(':checked') ) {
                $('#moove_gdpr_advanced_cookies').prop('checked', true).trigger('change'); // + 131022
              }
            } else {
              if ( $('#moove_gdpr_advanced_cookies').is(':checked') ) {
                $('#moove_gdpr_advanced_cookies').prop('checked', false).trigger('change'); // + 131022
              }
            }

            // WP Consent API - statistics and marketing follow the same categories as the
            // Google consent signals, so the two agree on a site that has switched one of
            // Analytics and Marketing off.
            var signal_cats = gdpr_consent_signal_cats();
            gdpr_wp_set_consent( 'statistics', parseInt( cookies[ signal_cats.analytics ] ) === 1 ? 'allow' : 'deny' );
            gdpr_wp_set_consent( 'marketing', parseInt( cookies[ signal_cats.ads ] ) === 1 ? 'allow' : 'deny' );

            if ( parseInt( cookies.performance ) === 1 ) {
              if ( ! $('#moove_gdpr_performance_cc_cookies').is(':checked') ) {
                $('#moove_gdpr_performance_cc_cookies').prop('checked', true).trigger('change'); // + 131022
              }

              // WP Consent API
              gdpr_wp_set_consent( 'statistics-anonymous', 'allow' );

            } else {
              if ( $('#moove_gdpr_performance_cc_cookies').is(':checked') ) {
                $('#moove_gdpr_performance_cc_cookies').prop('checked', false).trigger('change'); // + 131022
              }

              // WP Consent API
              gdpr_wp_set_consent( 'statistics-anonymous', 'deny' );
            }

            if ( parseInt( cookies.preference ) === 1 ) {
              if ( ! $('#moove_gdpr_preference_cc_cookies').is(':checked') ) {
                $('#moove_gdpr_preference_cc_cookies').prop('checked', true).trigger('change'); // + 131022
              }

              // WP Consent API
              gdpr_wp_set_consent( 'preferences', 'allow' );

            } else {
              if ( $('#moove_gdpr_preference_cc_cookies').is(':checked') ) {
                $('#moove_gdpr_preference_cc_cookies').prop('checked', false).trigger('change'); // + 131022
              }

              // WP Consent API
              gdpr_wp_set_consent( 'preferences', 'deny' );
            }

            $('input[data-name="moove_gdpr_performance_cookies"]').prop('checked',$('#moove_gdpr_performance_cookies').is(':checked'));
            $('input[data-name="moove_gdpr_strict_cookies"]').prop('checked',$('#moove_gdpr_strict_cookies').is(':checked'));
            $('input[data-name="moove_gdpr_advanced_cookies"]').prop('checked',$('#moove_gdpr_advanced_cookies').is(':checked'));
            $('input[data-name="moove_gdpr_performance_cc_cookies"]').prop('checked',$('#moove_gdpr_performance_cc_cookies').is(':checked'));
            $('input[data-name="moove_gdpr_preference_cc_cookies"]').prop('checked',$('#moove_gdpr_preference_cc_cookies').is(':checked'));
            moove_gdpr_sync_disabled_notes();
          }
        }

        function moove_gdpr_hide_infobar() {
          if ( $('#moove_gdpr_cookie_info_bar').length > 0 ) {
            $('#moove_gdpr_cookie_info_bar').addClass('moove-gdpr-info-bar-hidden');
            $('body').removeClass('gdpr-infobar-visible');
            $('#moove_gdpr_cookie_info_bar').hide();
          }
        }

        var gdpr_infobar_moved = false;
        var gdpr_scroll_padding_side = null;

        /**
         * Keeps keyboard focus from landing underneath the banner (WCAG 2.4.11). While the banner is
         * visible, the page's scroll padding on the banner's edge is raised to the banner's height,
         * so browsers scroll focused elements clear of it. A theme's own scroll padding is kept if
         * it is larger. The full-screen banner blocks the page, so it needs none.
         */
        function moove_gdpr_sync_scroll_padding() {
          var infobar = document.getElementById('moove_gdpr_cookie_info_bar');
          var root    = document.documentElement;

          if ( gdpr_scroll_padding_side ) {
            root.style.removeProperty( 'scroll-padding-' + gdpr_scroll_padding_side );
            gdpr_scroll_padding_side = null;
          }

          if ( ! infobar || ! $(infobar).is(':visible') || $(infobar).hasClass('gdpr-full-screen-infobar') ) {
            return;
          }

          var side     = $(infobar).hasClass('gdpr_infobar_postion_top') ? 'top' : 'bottom';
          var existing = parseFloat( window.getComputedStyle( root ).getPropertyValue( 'scroll-padding-' + side ) ) || 0;
          root.style.setProperty( 'scroll-padding-' + side, Math.max( infobar.offsetHeight, existing ) + 'px' );
          gdpr_scroll_padding_side = side;
        }

        // The box inside the full-screen banner, which is exposed as a modal dialog. Null for the regular banner.
        function moove_gdpr_infobar_dialog() {
          var infobar = $('#moove_gdpr_cookie_info_bar.gdpr-full-screen-infobar');
          return infobar.length > 0 ? infobar.find('.moove-gdpr-info-bar-container')[0] || null : null;
        }

        /**
         * Runs before the banner is first shown.
         *
         * The banner is printed on wp_footer, which puts it last in reading and Tab order, so
         * screen reader and keyboard users reach it only after the whole page. Moving it to the
         * start of <body> fixes that. It is position: fixed, so where it appears does not change.
         * It is moved once only: later moves would drop focus from inside it.
         *
         * The full-screen banner covers and blocks the page, so its box is exposed as a modal dialog.
         */
        function moove_gdpr_prepare_infobar() {
          var infobar = document.getElementById('moove_gdpr_cookie_info_bar');
          if ( ! infobar ) {
            return;
          }

          if ( ! gdpr_infobar_moved ) {
            if ( document.body.firstChild !== infobar ) {
              document.body.insertBefore( infobar, document.body.firstChild );
            }
            // Size changes include the banner being hidden, so this also clears the padding.
            if ( typeof window.ResizeObserver === 'function' ) {
              new window.ResizeObserver( moove_gdpr_sync_scroll_padding ).observe( infobar );
            }
            $(window).on( 'resize', moove_gdpr_sync_scroll_padding );
          }
          gdpr_infobar_moved = true;

          var dialog = moove_gdpr_infobar_dialog();
          if ( dialog ) {
            $(dialog).attr({ 'role': 'dialog', 'aria-modal': 'true', 'tabindex': '-1' });
            if ( document.getElementById('moove_gdpr_cookie_info_bar_title') ) {
              $(dialog).attr( 'aria-labelledby', 'moove_gdpr_cookie_info_bar_title' );
            }
          }
        }

        /**
         * Moves focus to the cookie banner once it is visible.
         *
         * The full-screen banner always takes focus, as any modal dialog must. The regular
         * banner does so only when the "Accessibility" option is set to "Cookie Banner", and
         * only while the document itself still holds focus - if the visitor has already tabbed
         * or clicked into the page (the banner can appear after a configurable delay), their
         * position is left alone.
         */
        function moove_gdpr_focus_infobar() {
          var $infobar = $('#moove_gdpr_cookie_info_bar');
          if ( $infobar.length === 0 || ! $infobar.is(':visible') ) {
            return;
          }

          var dialog = moove_gdpr_infobar_dialog();
          if ( dialog ) {
            dialog.focus();
            return;
          }

          if ( typeof moove_frontend_gdpr_scripts.gdpr_focus_cb === 'undefined' || moove_frontend_gdpr_scripts.gdpr_focus_cb !== 'true' ) {
            return;
          }

          var active = document.activeElement;
          if ( active && active !== document.body && active !== document.documentElement ) {
            return;
          }

          $infobar[0].focus();
        }

        function moove_gdpr_show_infobar() {
          var show_infobar = true;
          if ( typeof( sessionStorage ) !== "undefined" && parseInt(sessionStorage.getItem( 'gdpr_infobar_hidden' )) === 1 ) {
            show_infobar = false;
          }

          if ( typeof moove_frontend_gdpr_scripts.display_cookie_banner !== 'undefined' && show_infobar ) {
            if ( moove_frontend_gdpr_scripts.display_cookie_banner === 'true' ) {
              if ( $('#moove_gdpr_cookie_info_bar').length > 0 ) {
                $('#moove_gdpr_cookie_info_bar').removeClass('moove-gdpr-info-bar-hidden');
                $('#moove_gdpr_save_popup_settings_button:not(.button-visible)').hide();
                $('body').addClass('gdpr-infobar-visible');
                moove_gdpr_prepare_infobar();
                $('#moove_gdpr_cookie_info_bar').show();
                moove_gdpr_focus_infobar();
                gdpr_save_analytics( 'show_infobar', '' );
              }
            } else {
              if ( $('#moove_gdpr_cookie_info_bar').length > 0 ) {
                $('#moove_gdpr_cookie_info_bar').addClass('moove-gdpr-info-bar-hidden');
                $('body').removeClass('gdpr-infobar-visible');
                $('#moove_gdpr_cookie_info_bar').hide();
                var load_cookies = {
                  "strict" : 1,
                  "thirdparty" : 1,
                  "advanced" : 1,
                  "performance": 1,
                  "preference": 1
                };
                gdpr_cc_log('dbg - inject - 5');
                moove_gdpr_inject_scripts_on_load( JSON.stringify( load_cookies ) );
              }
            }
          } else {
            if ( $('#moove_gdpr_cookie_info_bar').length > 0 && show_infobar ) {
              $('#moove_gdpr_cookie_info_bar').removeClass('moove-gdpr-info-bar-hidden');
              $('#moove_gdpr_save_popup_settings_button:not(.button-visible)').hide();
              $('body').addClass('gdpr-infobar-visible');
              moove_gdpr_prepare_infobar();
              $('#moove_gdpr_cookie_info_bar').show();
              moove_gdpr_focus_infobar();
              gdpr_save_analytics( 'show_infobar', '' );
            }
          }
        }

        $(document).on('click tap','#moove_gdpr_cookie_info_bar .moove-gdpr-infobar-close-btn', function(e) {
          e.preventDefault();
          
          if ( typeof moove_frontend_gdpr_scripts.close_btn_action !== 'undefined' ) {
            var close_btn_action = parseInt( moove_frontend_gdpr_scripts.close_btn_action );

            if ( close_btn_action === 1 ) {
              moove_gdpr_hide_infobar();
              $('#moove_gdpr_save_popup_settings_button').show();
              if ( typeof( sessionStorage ) !== "undefined" ) {
                sessionStorage.setItem( 'gdpr_infobar_hidden', 1 );
              }
            }

            if ( close_btn_action === 2 ) {
              gdpr_delete_all_cookies();
              gdpr_ajax_delete_cookies();

              if ( $('#moove_gdpr_cookie_info_bar').length > 0 ) {
                $('#moove_gdpr_cookie_info_bar').addClass('moove-gdpr-info-bar-hidden');
                $('body').removeClass('gdpr-infobar-visible');
                $('#moove_gdpr_cookie_info_bar').hide();
                $('#moove_gdpr_save_popup_settings_button').show();
              }

              $('.gdpr_lightbox .gdpr_lightbox-close').trigger('click');
              $(document).moove_gdpr_lightbox_close();

              if ( ( typeof moove_frontend_gdpr_scripts.gdpr_scor !== 'undefined' ) && moove_frontend_gdpr_scripts.gdpr_scor === 'false' )  {
              } else {
                moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: '1', thirdparty: '0', advanced: '0', performance: '0', preference: '0'}),cookie_expiration);
                setTimeout(function(){
                  moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: '1', thirdparty: '0', advanced: '0', performance: '0', preference: '0'}),cookie_expiration);
                }, 500);
              }
              
              moove_gdpr_check_reload( 'reject-btn' );
            }

            if ( close_btn_action === 3 ) {
              moove_gdpr_save_cookies( 'enable_all close-btn' );
            }

            // Close With Redirect
            if ( close_btn_action === 4 ) {
              gdpr_delete_all_cookies();
              gdpr_ajax_delete_cookies();

              if ( $('#moove_gdpr_cookie_info_bar').length > 0 ) {
                $('#moove_gdpr_cookie_info_bar').addClass('moove-gdpr-info-bar-hidden');
                $('body').removeClass('gdpr-infobar-visible');
                $('#moove_gdpr_cookie_info_bar').hide();
                $('#moove_gdpr_save_popup_settings_button').show();
              }

              $('.gdpr_lightbox .gdpr_lightbox-close').trigger('click');
              $(document).moove_gdpr_lightbox_close();

              if ( ( typeof moove_frontend_gdpr_scripts.gdpr_scor !== 'undefined' ) && moove_frontend_gdpr_scripts.gdpr_scor === 'false' )  {
              } else {
                moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: '1', thirdparty: '0', advanced: '0', performance: '0', preference: '0'}),cookie_expiration);
                setTimeout(function(){
                  moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: '1', thirdparty: '0', advanced: '0', performance: '0', preference: '0'}),cookie_expiration);
                }, 500);
              }

              if ( ( typeof moove_frontend_gdpr_scripts.close_btn_rdr !== 'undefined' ) && moove_frontend_gdpr_scripts.close_btn_rdr !== '' )  {
                window.parent.location.href = moove_frontend_gdpr_scripts.close_btn_rdr;
              } else {
                moove_gdpr_check_reload( 'reject-btn' );
              }
            }
          } else {
            moove_gdpr_hide_infobar();
            $('#moove_gdpr_save_popup_settings_button').show();
            if ( typeof( sessionStorage ) !== "undefined" ) {
              sessionStorage.setItem( 'gdpr_infobar_hidden', 1 );
            }
          }

        });

        function moove_gdpr_create_cookie(name, value, days) {

          var expires;
          if (days > 0) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toGMTString();
          } else {
            expires = "";
          }
          try {
            var cookie_attributes =  'SameSite=Lax';
            if ( typeof moove_frontend_gdpr_scripts.cookie_attributes !== 'undefined' ) {
              cookie_attributes = moove_frontend_gdpr_scripts.cookie_attributes;
            }
            if ( typeof moove_frontend_gdpr_scripts.gdpr_consent_version !== 'undefined' ) {
              value = JSON.parse( value );
              value.version = moove_frontend_gdpr_scripts.gdpr_consent_version;
              value = JSON.stringify( value );
            }

            if ( name === 'moove_gdpr_popup' ) {
              if ( parseInt( value.strict ) === 0 ) {
                if ( ( typeof moove_frontend_gdpr_scripts.gdpr_scor !== 'undefined' ) && moove_frontend_gdpr_scripts.gdpr_scor === 'false' )  {
                  document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/; " + cookie_attributes;
                } else {
                  document.cookie = encodeURIComponent(name) +'=; Path=/;';
                }
              } else {
                document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/; " + cookie_attributes;
              }
            } else {
              document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/; " + cookie_attributes;
            }            
            if ( value !== consent_values ) {
              consent_values = value;
              gdpr_save_consent_log( value );
            }
            // Update global consent variables when user switches consent
            if ( name === 'moove_gdpr_popup' ) {
              try {
                var _cv = typeof value === 'string' ? JSON.parse(value) : value;
                gdpr_consent__strict = _cv.strict === '1' || _cv.strict === 1 ? 'true' : 'false';
                gdpr_consent__thirdparty = _cv.thirdparty === '1' || _cv.thirdparty === 1 ? 'true' : 'false';
                gdpr_consent__advanced = _cv.advanced === '1' || _cv.advanced === 1 ? 'true' : 'false';
                gdpr_consent__performance = _cv.performance === '1' || _cv.performance === 1 ? 'true' : 'false';
                gdpr_consent__preference = _cv.preference === '1' || _cv.preference === 1 ? 'true' : 'false';
                var _cc = [];
                if (_cv.strict === '1' || _cv.strict === 1) _cc.push('strict');
                if (_cv.thirdparty === '1' || _cv.thirdparty === 1) _cc.push('thirdparty');
                if (_cv.advanced === '1' || _cv.advanced === 1) _cc.push('advanced');
                if (_cv.performance === '1' || _cv.performance === 1) _cc.push('performance');
                if (_cv.preference === '1' || _cv.preference === 1) _cc.push('preference');
                gdpr_consent__cookies = _cc.join('|');
              } catch(e) {}
            }
          } catch(e) {
            gdpr_cc_log('error - moove_gdpr_create_cookie: ' + e);
          }
        }


        function moove_gdpr_read_cookie(name) {
          var nameEQ = encodeURIComponent(name) + "=";
          var ca = document.cookie.split(';');
          for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) {
              var cookie_value  =  decodeURIComponent(c.substring(nameEQ.length, c.length));
              var cookie_json   = JSON.parse( cookie_value );
              if ( typeof cookie_json.version !== 'undefined' ) {
                if ( typeof moove_frontend_gdpr_scripts.gdpr_consent_version !== 'undefined' ) {
                  var current_ver = moove_frontend_gdpr_scripts.gdpr_consent_version;
                  if ( parseFloat( current_ver ) > parseFloat( cookie_json.version ) ) {
                    document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                    return null;
                  }
                }
              } else {
                if ( typeof moove_frontend_gdpr_scripts.gdpr_consent_version !== 'undefined' && parseFloat( moove_frontend_gdpr_scripts.gdpr_consent_version ) > 1 ) {
                  document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                  return null;
                }
              }
              return cookie_value;
            }
          }
          return null;
        }

        function moove_gdpr_check_append_html(el, str) {
          var div = document.createElement('div');
          div.innerHTML = str;
          while (div.children.length > 0) {
            el.appendChild(div.children[0]);
          }
        }

        function moove_gdpr_inject_scripts_on_load(cookies) {          
          initial_cookies = m_g_read_cookies();

          if ( cookies ) {
            var cookie_input = cookies;
            cookies = JSON.parse( cookies );
            var cookies_json = m_g_read_cookies();       

            if ( injected_scripts !== false ) {
              var already_injected = JSON.parse( injected_scripts );              

              if ( parseInt( already_injected.thirdparty ) === 1 && parseInt( cookies.thirdparty ) === 1 ) {
                cookies.thirdparty = '0';
              }

              if ( parseInt( already_injected.advanced ) === 1 && parseInt( cookies.advanced ) === 1 ) {
                cookies.advanced = '0';
              }

              if ( parseInt( already_injected.performance ) === 1 && parseInt( cookies.performance ) === 1 ) {
                cookies.performance = '0';
              }

              if ( parseInt( already_injected.preference ) === 1 && parseInt( cookies.preference ) === 1 ) {
                cookies.preference = '0';
              }
            }

            gdpr_save_analytics( 'script_inject', cookies );
            is_created = true;    
                   
            if ( typeof moove_frontend_gdpr_scripts.ifbc !== 'undefined' ) {
              if ( moove_frontend_gdpr_scripts.ifbc === 'strict' && cookies && parseInt( cookies.strict ) === 1 ) {
                gdpr_remove_iframe_restrictions();
              }

              if ( moove_frontend_gdpr_scripts.ifbc === 'thirdparty' && cookies && parseInt( cookies.thirdparty ) === 1 ) {
                gdpr_remove_iframe_restrictions();
              }

              if ( moove_frontend_gdpr_scripts.ifbc === 'advanced' && cookies && parseInt( cookies.advanced ) === 1 ) {
                gdpr_remove_iframe_restrictions();
              }

              if ( moove_frontend_gdpr_scripts.ifbc === 'performance' && cookies && parseInt( cookies.performance ) === 1 ) {
                gdpr_remove_iframe_restrictions();
              }

              if ( moove_frontend_gdpr_scripts.ifbc === 'preference' && cookies && parseInt( cookies.preference ) === 1 ) {
                gdpr_remove_iframe_restrictions();
              }
              

            } else {
              if ( parseInt( cookies.strict ) === 1 ) {
                gdpr_remove_iframe_restrictions();
              }
            }

            if ( typeof moove_frontend_gdpr_scripts.scripts_defined !== 'undefined' ) {
              try {
                var scripts_defined = JSON.parse( moove_frontend_gdpr_scripts.scripts_defined );

                if ( ( typeof cookies.strict !== 'undefined' && parseInt( cookies.strict ) === 1 ) || parseInt( moove_frontend_gdpr_scripts.enabled_default.strict ) > 1 ) {
                  if ( ( ( cookies.strict !== 'undefined' && parseInt( cookies.strict ) === 1 ) || parseInt( moove_frontend_gdpr_scripts.enabled_default.strict ) > 1 ) && typeof gdpr_cookies_loaded.strict === 'undefined' ) {
                    if ( typeof scripts_defined.strict !== 'undefined' && scripts_defined.strict.header ) {
                      gdpr_postscribe(document.head, scripts_defined.strict.header);
                    }
                    if ( typeof scripts_defined.strict !== 'undefined' && scripts_defined.strict.body ) {
                      $(scripts_defined.strict.body).prependTo(document.body);
                    }

                    if ( typeof scripts_defined.strict !== 'undefined' && scripts_defined.strict.footer ) {
                      gdpr_postscribe(document.body, scripts_defined.strict.footer);
                    }
                    gdpr_cookies_loaded.strict = true;
                  } 

                  if ( parseInt( cookies.thirdparty ) === 1 && typeof gdpr_cookies_loaded.thirdparty === 'undefined' ) {
                    if ( scripts_defined.thirdparty.header ) {
                      gdpr_postscribe(document.head, scripts_defined.thirdparty.header);
                    }
                    if ( scripts_defined.thirdparty.body ) {
                      $(scripts_defined.thirdparty.body).prependTo(document.body);
                    }

                    if ( scripts_defined.thirdparty.footer ) {
                      gdpr_postscribe(document.body, scripts_defined.thirdparty.footer);
                    }
                    gdpr_cookies_loaded.thirdparty = true;
                  } 

                  if ( parseInt( cookies.advanced ) === 1  && typeof gdpr_cookies_loaded.advanced === 'undefined' ) {
                    if ( scripts_defined.advanced.header ) {
                      gdpr_postscribe(document.head, scripts_defined.advanced.header);
                    }
                    if ( scripts_defined.advanced.body ) {
                      $(scripts_defined.advanced.body).prependTo(document.body);
                    }
                    if ( scripts_defined.advanced.footer ) {
                      gdpr_postscribe(document.body, scripts_defined.advanced.footer);
                    }
                    gdpr_cookies_loaded.advanced = true;
                  }

                 
                  if ( typeof cookies.performance !== 'undefined' && parseInt( cookies.performance ) === 1  && typeof gdpr_cookies_loaded.performance === 'undefined' ) {
                    if ( typeof scripts_defined.performance !== 'undefined' && scripts_defined.performance.header ) {
                      gdpr_postscribe(document.head, scripts_defined.performance.header);
                    }
                    if ( typeof scripts_defined.performance !== 'undefined' && scripts_defined.performance.body ) {
                      $(scripts_defined.performance.body).prependTo(document.body);
                    }
                    if ( typeof scripts_defined.performance !== 'undefined' && scripts_defined.performance.footer ) {
                      gdpr_postscribe(document.body, scripts_defined.performance.footer);
                    }
                    gdpr_cookies_loaded.performance = true;
                  }

                  if ( typeof cookies.preference !== 'undefined' && parseInt( cookies.preference ) === 1  && typeof gdpr_cookies_loaded.preference === 'undefined' ) {
                    if ( typeof scripts_defined.preference !== 'undefined' && scripts_defined.preference.header ) {
                      gdpr_postscribe(document.head, scripts_defined.preference.header);
                    }
                    if ( typeof scripts_defined.preference !== 'undefined' && scripts_defined.preference.body ) {
                      $(scripts_defined.preference.body).prependTo(document.body);
                    }
                    if ( typeof scripts_defined.preference !== 'undefined' && scripts_defined.preference.footer ) {
                      gdpr_postscribe(document.body, scripts_defined.preference.footer);
                    }
                    gdpr_cookies_loaded.preference = true;
                  }


                } else {
                  var cookies = moove_gdpr_read_cookie('moove_gdpr_popup');
                  if ( cookies ) {
                    gdpr_delete_all_cookies();
                    gdpr_ajax_delete_cookies();
                  }
                }
              } catch( e ) {
                // Error occurred
                console.warn('1');
                console.error(e);
              }
              
            } else {
              if ( typeof gdpr_cookies_loaded.strict === 'undefined' || typeof gdpr_cookies_loaded.thirdparty === 'undefined' || typeof gdpr_cookies_loaded.advanced === 'undefined' ) {
                if ( cookies.strict === 1 ) {
                  gdpr_cookies_loaded.strict  = true;
                }
                if ( cookies.thirdparty === 1 ) {
                  gdpr_cookies_loaded.thirdparty  = true;
                }
                if ( cookies.advanced === 1 ) {
                  gdpr_cookies_loaded.advanced    = true;
                }
                if ( cookies.performance === 1 ) {
                  gdpr_cookies_loaded.performance    = true;
                }
                if ( cookies.preference === 1 ) {
                  gdpr_cookies_loaded.preference    = true;
                }

                var wp_lang = typeof moove_frontend_gdpr_scripts.wp_lang !== 'undefined' ? moove_frontend_gdpr_scripts.wp_lang : '';
                
                if ( parseInt( cookies.strict ) === 0 && parseInt( cookies.thirdparty ) === 0 && parseInt( cookies.advanced ) === 0 && parseInt( cookies.performance ) === 0 && parseInt( cookies.preference ) === 0 ) {
                  gdpr_delete_all_cookies();
                }

                $.post(
                  moove_frontend_gdpr_scripts.ajaxurl,
                  {
                    action: "moove_gdpr_get_scripts",
                    strict: cookies.strict,
                    thirdparty: cookies.thirdparty,
                    advanced: cookies.advanced,
                    performance: cookies.performance,
                    preference: cookies.preference,
                    wp_lang: wp_lang,
                  },
                  function( msg ) {
                    injected_scripts = cookie_input;

                    gdpr_save_analytics( 'script_inject', cookies );

                    var obj = typeof msg === 'string' ? JSON.parse( msg ) : msg;
                    if ( obj.header ) {
                      gdpr_postscribe(document.head, obj.header);
                    }
                    if ( obj.body ) {
                      $(obj.body).prependTo(document.body);
                    }

                    if ( obj.footer ) {
                      gdpr_postscribe(document.body, obj.footer);
                    }
                  }
                );
              }
            }
            
          } else {
            moove_gdpr_show_infobar();
          }
        }

        $.fn.moove_gdpr_save_cookie = function(options){
          
          var cookies = moove_gdpr_read_cookie('moove_gdpr_popup');

          var cookie_input = cookies;
          
          var initial_scroll = $(window).scrollTop();

          if ( ! cookies ) {
            if ( options.thirdParty ) {
              var thirdparty = '1';
            } else {
              var thirdparty = '0';
            }

            if ( options.advanced ) {
              var advanced = '1';
            } else {
              var advanced = '0';
            }

            if ( options.performance ) {
              var performance = '1';
            } else {
              var performance = '0';
            }

            if ( options.preference ) {
              var preference = '1';
            } else {
              var preference = '0';
            }

            if ( options.scrollEnable ) {
              var scroll_offset = options.scrollEnable;
              $( window ).scroll(function() {
                if ( !is_created && ( $(this).scrollTop() - initial_scroll ) > scroll_offset ) {
                  if ( options.thirdparty !== 'undefined' || options.advanced !== 'undefined' ) {
                    moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: '1', thirdparty: thirdparty, advanced: advanced, performance: performance, preference: preference}),cookie_expiration);
                    cookies = JSON.parse(cookies);
                    moove_gdpr_change_switchers(cookies);
                  }
                }
              });

            } else {
              if ( options.thirdparty !== 'undefined' || options.advanced !== 'undefined' ) {
                moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: '1', thirdparty: thirdparty, advanced: advanced, performance: performance, preference: preference}),cookie_expiration);
                cookies = JSON.parse(cookies);
                moove_gdpr_change_switchers(cookies);
              }
            }
            cookies = moove_gdpr_read_cookie('moove_gdpr_popup');

            if ( cookies ) {
              cookies = JSON.parse( cookies );
              gdpr_save_analytics( 'script_inject', cookies );
              is_created = true;

              if ( typeof moove_frontend_gdpr_scripts.ifbc !== 'undefined' ) {
                if ( moove_frontend_gdpr_scripts.ifbc === 'strict' && cookies && parseInt( cookies.strict ) === 1 ) {
                  gdpr_remove_iframe_restrictions();
                }

                if ( moove_frontend_gdpr_scripts.ifbc === 'thirdparty' && cookies && parseInt( cookies.thirdparty ) === 1 ) {
                  gdpr_remove_iframe_restrictions();
                }

                if ( moove_frontend_gdpr_scripts.ifbc === 'advanced' && cookies && parseInt( cookies.advanced ) === 1 ) {
                  gdpr_remove_iframe_restrictions();
                }

                if ( moove_frontend_gdpr_scripts.ifbc === 'performance' && cookies && parseInt( cookies.performance ) === 1 ) {
                  gdpr_remove_iframe_restrictions();
                }

                if ( moove_frontend_gdpr_scripts.ifbc === 'preference' && cookies && parseInt( cookies.preference ) === 1 ) {
                  gdpr_remove_iframe_restrictions();
                }

              } else {
                if ( parseInt( cookies.strict ) === 1 ) {
                  gdpr_remove_iframe_restrictions();
                }
              }

              if ( typeof moove_frontend_gdpr_scripts.scripts_defined !== 'undefined' ) {
                try {
                  var scripts_defined = JSON.parse( moove_frontend_gdpr_scripts.scripts_defined );    

                  if ( ( typeof cookies.strict !== 'undefined' && parseInt( cookies.strict ) === 1 ) || parseInt( moove_frontend_gdpr_scripts.enabled_default.strict ) > 1 ) {
                    if ( ( ( cookies.strict !== 'undefined' && parseInt( cookies.strict ) === 1 ) || parseInt( moove_frontend_gdpr_scripts.enabled_default.strict ) > 1 ) && typeof gdpr_cookies_loaded.strict === 'undefined' ) {
                      if ( typeof scripts_defined.strict !== 'undefined' && scripts_defined.strict.header ) {
                        gdpr_postscribe(document.head, scripts_defined.strict.header);
                      }
                      if ( typeof scripts_defined.strict !== 'undefined' && scripts_defined.strict.body ) {
                        $(scripts_defined.strict.body).prependTo(document.body);
                      }

                      if ( typeof scripts_defined.strict !== 'undefined' && scripts_defined.strict.footer ) {
                        gdpr_postscribe(document.body, scripts_defined.strict.footer);
                      }
                      gdpr_cookies_loaded.strict = true;
                    } 

                    if ( parseInt( cookies.thirdparty ) === 1 && typeof gdpr_cookies_loaded.thirdparty === 'undefined' ) {
                      if ( scripts_defined.thirdparty.header ) {
                        gdpr_postscribe(document.head, scripts_defined.thirdparty.header);
                      }
                      if ( scripts_defined.thirdparty.body ) {
                        $(scripts_defined.thirdparty.body).prependTo(document.body);
                      }

                      if ( scripts_defined.thirdparty.footer ) {
                        gdpr_postscribe(document.body, scripts_defined.thirdparty.footer);
                      }
                      gdpr_cookies_loaded.thirdparty = true;
                    } 
                    if ( parseInt( cookies.advanced ) === 1  && typeof gdpr_cookies_loaded.advanced === 'undefined' ) {
                      if ( scripts_defined.advanced.header ) {
                        gdpr_postscribe(document.head, scripts_defined.advanced.header);
                      }
                      if ( scripts_defined.advanced.body ) {
                        $(scripts_defined.advanced.body).prependTo(document.body);
                      }
                      if ( scripts_defined.advanced.footer ) {
                        gdpr_postscribe(document.body, scripts_defined.advanced.footer);
                      }
                      gdpr_cookies_loaded.advanced = true;
                    }

                    if ( typeof cookies.performance !== 'undefined' && parseInt( cookies.performance ) === 1  && typeof gdpr_cookies_loaded.performance === 'undefined' ) {
                      if ( typeof scripts_defined.performance !== 'undefined' && scripts_defined.performance.header ) {
                        gdpr_postscribe(document.head, scripts_defined.performance.header);
                      }
                      if ( typeof scripts_defined.performance !== 'undefined' && scripts_defined.performance.body ) {
                        $(scripts_defined.performance.body).prependTo(document.body);
                      }
                      if ( typeof scripts_defined.performance !== 'undefined' && scripts_defined.performance.footer ) {
                        gdpr_postscribe(document.body, scripts_defined.performance.footer);
                      }
                      gdpr_cookies_loaded.performance = true;
                    }

                    if ( typeof cookies.preference !== 'undefined' && parseInt( cookies.preference ) === 1  && typeof gdpr_cookies_loaded.preference === 'undefined' ) {
                      if ( typeof scripts_defined.preference !== 'undefined' && scripts_defined.preference.header ) {
                        gdpr_postscribe(document.head, scripts_defined.preference.header);
                      }
                      if ( typeof scripts_defined.preference !== 'undefined' && scripts_defined.preference.body ) {
                        $(scripts_defined.preference.body).prependTo(document.body);
                      }
                      if ( typeof scripts_defined.preference !== 'undefined' && scripts_defined.preference.footer ) {
                        gdpr_postscribe(document.body, scripts_defined.preference.footer);
                      }
                      gdpr_cookies_loaded.preference = true;
                    }
                    // console.warn(cookies);
                  } else {
                    var cookies = moove_gdpr_read_cookie('moove_gdpr_popup');
                    if ( cookies ) {
                      gdpr_delete_all_cookies();
                      gdpr_ajax_delete_cookies();
                    }
                  }
                } catch( e ) {
                  // Error occurred
                  console.warn('2');
                  console.error(e);
                }
                
              } else {
                if ( typeof gdpr_cookies_loaded.thirdparty === 'undefined' || typeof gdpr_cookies_loaded.advanced === 'undefined' || typeof gdpr_cookies_loaded.performance === 'undefined' || typeof gdpr_cookies_loaded.preference === 'undefined' ) {
                  if ( cookies.thirdparty === 1 ) {
                    gdpr_cookies_loaded.thirdparty  = true;
                  }
                  if ( cookies.advanced === 1 ) {
                    gdpr_cookies_loaded.advanced    = true;
                  }
                  if ( cookies.performance === 1 ) {
                    gdpr_cookies_loaded.performance    = true;
                  }
                  if ( cookies.preference === 1 ) {
                    gdpr_cookies_loaded.preference    = true;
                  }
                  
                  var wp_lang = typeof moove_frontend_gdpr_scripts.wp_lang !== 'undefined' ? moove_frontend_gdpr_scripts.wp_lang : '';
                  
                  if ( parseInt( cookies.thirdparty ) === 0 && parseInt( cookies.advanced ) === 0 && parseInt( cookies.performance ) === 0 && parseInt( cookies.preference ) === 0 ) {
                    gdpr_delete_all_cookies();
                  }

                  $.post(
                    moove_frontend_gdpr_scripts.ajaxurl,
                    {
                      action: "moove_gdpr_get_scripts",
                      strict: cookies.strict,
                      thirdparty: cookies.thirdparty,
                      advanced: cookies.advanced,
                      performance: cookies.performance,
                      preference: cookies.preference,
                      wp_lang : wp_lang,
                    },
                    function( msg ) {
                      injected_scripts = cookie_input;

                      gdpr_save_analytics( 'script_inject', cookies );
                      var obj = typeof msg === 'string' ? JSON.parse( msg ) : msg;
                      if ( obj.header ) {
                        gdpr_postscribe(document.head, obj.header);
                      }
                      if ( obj.body ) {
                        $(obj.body).prependTo(document.body);
                      }

                      if ( obj.footer ) {
                        gdpr_postscribe(document.body, obj.footer);
                      }
                    }
                  );
                }
              }
            }
          }
        };

        /**
         * Add-on option to hide the banner after the visitor scrolls, or after a delay.
         *
         * This only hides the banner for the rest of the session. It never records consent or
         * loads scripts: scrolling or waiting is not a decision, and keyboard and screen reader
         * users scroll and pause while they are still reading. The banner is left alone while the
         * visitor is using it or the settings modal, and settings stay reachable afterwards from
         * the floating button or any settings link.
         */
        function moove_gdpr_init_auto_hide( initial_scroll ) {
          var modes = moove_frontend_gdpr_scripts.gdpr_aos_hide;
          if ( moove_frontend_gdpr_scripts.enable_on_scroll !== 'true' || typeof modes === 'undefined' ) {
            return;
          }

          var has_mode = function( mode ) {
            return modes === mode || ( mode === '1' && modes === 'true' ) || ( typeof modes === 'object' && modes !== null && modes.indexOf( mode ) !== -1 );
          };

          // Returns false while the visitor is still using the banner, so the caller can retry.
          var hide = function() {
            var infobar = document.getElementById('moove_gdpr_cookie_info_bar');
            if ( ! infobar || ! $(infobar).is(':visible') || moove_gdpr_read_cookie('moove_gdpr_popup') ) {
              return true;
            }
            if ( $('body').hasClass('moove_gdpr_overflow') || infobar === document.activeElement || $.contains( infobar, document.activeElement ) ) {
              return false;
            }
            moove_gdpr_hide_infobar();
            $('#moove_gdpr_save_popup_settings_button').show();
            if ( typeof( sessionStorage ) !== "undefined" ) {
              sessionStorage.setItem( 'gdpr_infobar_hidden', 1 );
            }
            return true;
          };

          if ( has_mode('1') ) {
            $(window).on('scroll.gdpr_auto_hide', function() {
              if ( $(window).scrollTop() - initial_scroll > 200 && hide() ) {
                $(window).off('scroll.gdpr_auto_hide');
              }
            });
          }

          if ( has_mode('2') ) {
            var seconds = parseInt( moove_frontend_gdpr_scripts.gdpr_aos_hide_seconds, 10 );
            var attempt = function() {
              if ( ! hide() ) {
                setTimeout( attempt, 5000 );
              }
            };
            setTimeout( attempt, ( isNaN( seconds ) ? 30 : seconds ) * 1000 );
          }
        }

        function moove_gdpr_check_cookie(){

          var path = location.pathname;
          var initial_scroll = $(window).scrollTop();
          $('#moove_gdpr_save_popup_settings_button').show();

          var default_strict = moove_frontend_gdpr_scripts.enabled_default.strict;
          var default_trirdparty = moove_frontend_gdpr_scripts.enabled_default.third_party;
          var default_advanced = moove_frontend_gdpr_scripts.enabled_default.advanced;

          var default_performance = typeof moove_frontend_gdpr_scripts.enabled_default.performance !== 'undefined' ? moove_frontend_gdpr_scripts.enabled_default.performance : false ;
          var default_preference = typeof moove_frontend_gdpr_scripts.enabled_default.preference !== 'undefined' ? moove_frontend_gdpr_scripts.enabled_default.preference : false ;

          if ( ( document.cookie.indexOf("moove_gdpr_popup") >= 0 ) || ( default_trirdparty == 1 || default_advanced == 1 || default_performance == 1 || default_preference == 1 || default_strict > 1 ) ) {

            var cookies = moove_gdpr_read_cookie('moove_gdpr_popup');
            if ( ! cookies ) {
              cookies = {
                "strict" : 1,
                "thirdparty" : default_trirdparty,
                "advanced" : default_advanced,
                "performance": default_performance,
                "preference": default_preference
              };

              moove_gdpr_change_switchers(cookies);
              cookies = JSON.stringify( cookies );

              moove_gdpr_show_infobar();
            } else {
              var cookies_json = m_g_read_cookies();
              
              if ( cookies_json.strict == '0' && cookies_json.thirdparty == '0' && cookies_json.advanced == '0' && cookies_json.performance == '0' && cookies_json.preference == '0' ) {                
                gdpr_delete_all_cookies();
                moove_gdpr_show_infobar();
              }
            }
            gdpr_cc_log('dbg - inject - 3');
            moove_gdpr_inject_scripts_on_load(cookies);
          } else {
            moove_gdpr_show_infobar();
          }

          moove_gdpr_init_auto_hide( initial_scroll );
        }
        moove_gdpr_check_cookie();



        $(document).on('click','[data-href*="#moove_gdpr_cookie_modal"],[href*="#moove_gdpr_cookie_modal"]',function(e){
          e.preventDefault();
          if ( $('#moove_gdpr_cookie_modal').length > 0 ) {
            is_gdpr_lightbox = true;
            modal_instance = gdpr_lightbox('#moove_gdpr_cookie_modal');
            // $('#moove_gdpr_strict_cookies').trigger('click').trigger('click');
            $('.gdpr_lightbox').addClass('moove_gdpr_cookie_modal_open');
            $(document).moove_gdpr_lightbox_open();
            gdpr_save_analytics( 'opened_modal_from_link', '' );
          }
        });

        $(document).on('click','[data-href*="#gdpr_cookie_modal"],[href*="#gdpr_cookie_modal"]',function(e){
          e.preventDefault();
          if ( $('#moove_gdpr_cookie_modal').length > 0 ) {
            is_gdpr_lightbox = true;
            modal_instance = gdpr_lightbox('#moove_gdpr_cookie_modal');
            // $('#moove_gdpr_strict_cookies').trigger('click').trigger('click');
            $('.gdpr_lightbox').addClass('moove_gdpr_cookie_modal_open');
            $(document).moove_gdpr_lightbox_open();
            gdpr_save_analytics( 'opened_modal_from_link', '' );
          }
        });



        function check_allow_button() {
          var hide_button = true;
          $(document).find('#moove_gdpr_cookie_modal input[type=checkbox]').each(function(){
            var checkbox = $(this);
            if ( ! checkbox.is(':checked') ) {
              hide_button = false;
            }
          });
        }

        $(document).on('click tap','#moove_gdpr_cookie_info_bar .moove-gdpr-close-modal-button a, #moove_gdpr_cookie_info_bar .moove-gdpr-close-modal-button button',function(e){
          e.preventDefault();
          // moove_gdpr_hide_infobar();
        });
        $(document).on('click tap','.moove-gdpr-modal-close',function(e){
          e.preventDefault();
          $('.gdpr_lightbox .gdpr_lightbox-close').trigger('click');
          $(document).moove_gdpr_lightbox_close();
        });

        $(document).on('click','#moove-gdpr-menu .moove-gdpr-tab-nav', function(e){
          e.preventDefault();
          e.stopPropagation();
          $('#moove-gdpr-menu li').removeClass('menu-item-selected');
          $(this).parent().addClass('menu-item-selected');
          // Keep the tablist state in sync with the visual selection. Only the selected tab is
          // in the Tab order; arrow keys move between tabs (see the tab list keydown handler).
          $('#moove-gdpr-menu .moove-gdpr-tab-nav').attr({ 'aria-selected': 'false', 'tabindex': '-1' });
          $(this).attr({ 'aria-selected': 'true', 'tabindex': '0' });
          $('.moove-gdpr-tab-content .moove-gdpr-tab-main').hide();
          $( $(this).attr('href') ).show();
          $( $(this).attr('data-href') ).show();

          gdpr_save_analytics( 'clicked_to_tab', $(this).attr('data-href') );

        });
        /*
         * Keyboard and screen reader support for the banner and the settings modal.
         *
         * Everything here relies on real DOM focus and on the native behaviour of buttons,
         * links and checkboxes. Only what native HTML does not already provide is handled:
         * keeping Tab inside modal surfaces, arrow keys in the tab list, and Enter/Space on
         * the few non-native elements that act as buttons.
         */
        var gdpr_focusable_selector = 'a[href], area[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), iframe, [contenteditable="true"], [tabindex]';
        var gdpr_narrow_viewport    = typeof window.matchMedia === 'function' ? window.matchMedia( '(max-width: 767px)' ) : null;

        function gdpr_is_narrow_viewport() {
          return gdpr_narrow_viewport ? gdpr_narrow_viewport.matches : window.innerWidth < 768;
        }

        function gdpr_get_focusable( container ) {
          return $(container).find( gdpr_focusable_selector ).filter( function() {
            return ! $(this).is('[tabindex^="-"]') && $(this).is(':visible');
          });
        }

        // Keeps Tab and Shift+Tab cycling inside a modal surface instead of reaching the page behind it.
        function gdpr_trap_focus( e, container ) {
          var focusable = gdpr_get_focusable( container );
          var active    = document.activeElement;
          var inside    = active === container || $.contains( container, active );

          if ( focusable.length === 0 ) {
            e.preventDefault();
            container.focus();
            return;
          }

          var first = focusable[0];
          var last  = focusable[ focusable.length - 1 ];

          if ( e.shiftKey && ( ! inside || active === first || active === container ) ) {
            e.preventDefault();
            last.focus();
          } else if ( ! e.shiftKey && ( ! inside || active === last ) ) {
            e.preventDefault();
            first.focus();
          }
        }

        /**
         * Category switches are disabled until strictly necessary cookies are on. Disabled
         * checkboxes cannot take focus and just read as "unavailable", so the reason is attached
         * as their description, and announced when someone clicks one.
         */
        function moove_gdpr_sync_disabled_notes() {
          var strict = document.getElementById('moove_gdpr_strict_cookies');
          var modal  = document.getElementById('moove_gdpr_cookie_modal');
          if ( ! strict || ! modal || typeof moove_frontend_gdpr_scripts.gdpr_a11y_strict_first !== 'string' ) {
            return;
          }

          var note = document.getElementById('moove_gdpr_disabled_note');
          if ( ! note ) {
            note = document.createElement('span');
            note.id = 'moove_gdpr_disabled_note';
            note.className = 'gdpr-sr-only';
            modal.appendChild( note );
          }
          note.textContent = moove_frontend_gdpr_scripts.gdpr_a11y_strict_first.replace( '%s', strict.getAttribute('aria-label') || '' );

          $(modal).find('.moove-gdpr-status-bar input[type=checkbox]').not( strict ).each( function() {
            if ( this.disabled && ! strict.checked ) {
              $(this).attr( 'aria-describedby', note.id );
            } else {
              $(this).removeAttr('aria-describedby');
            }
          });
        }

        // Polite announcement through a status region inside the modal. The region is created
        // when the modal opens, because screen readers ignore regions added with their message.
        function moove_gdpr_ensure_status_region() {
          var modal  = document.getElementById('moove_gdpr_cookie_modal');
          var region = document.getElementById('moove_gdpr_status');
          if ( ! region && modal ) {
            region = document.createElement('span');
            region.id = 'moove_gdpr_status';
            region.className = 'gdpr-sr-only';
            region.setAttribute( 'role', 'status' );
            modal.appendChild( region );
          }
          return region;
        }

        function moove_gdpr_announce( message ) {
          var region = moove_gdpr_ensure_status_region();
          if ( ! region || ! message ) {
            return;
          }
          // Cleared first so the same message is announced again on a repeat attempt.
          region.textContent = '';
          setTimeout( function() {
            region.textContent = message;
          }, 100 );
        }

        /**
         * Applies the semantics that match how the modal is presented right now:
         * - tabbed layout (v1), wide viewport: a tab list controlling tab panels
         * - tabbed layout (v1), narrow viewport: the menu is hidden and each section title
         *   expands its own content, so the titles become disclosure buttons
         * - one-page layout (v2): the menu is hidden and every section is shown, so the
         *   sections are plain headed content with no tab roles
         *
         * This lives in the script rather than the templates because the right roles change
         * with the viewport, and it also covers tabs added by older add-on versions and
         * theme-overridden templates that predate these attributes.
         */
        function moove_gdpr_sync_modal_semantics() {
          var modal_content = $('#moove_gdpr_cookie_modal .moove-gdpr-modal-content');
          if ( modal_content.length === 0 ) {
            return;
          }

          var is_tabbed_layout = modal_content.hasClass('moove_gdpr_modal_theme_v1');
          var is_tabs          = is_tabbed_layout && ! gdpr_is_narrow_viewport();
          var is_accordion     = is_tabbed_layout && gdpr_is_narrow_viewport();

          // The desktop tab list is a vertical column.
          $('#moove-gdpr-menu').attr({ 'role': 'tablist', 'aria-orientation': is_tabs ? 'vertical' : null }).children('li').attr('role', 'presentation');
          $('#moove-gdpr-menu .moove-gdpr-tab-nav').each( function() {
            var tab      = $(this);
            var panel_id = ( tab.attr('data-href') || '' ).replace( '#', '' );
            var selected = tab.parent().hasClass('menu-item-selected');

            if ( ! tab.attr('id') && panel_id ) {
              tab.attr( 'id', 'gdpr-tab-' + panel_id );
            }

            tab.attr({
              'role': 'tab',
              'aria-controls': panel_id,
              'aria-selected': selected ? 'true' : 'false',
              'tabindex': selected ? '0' : '-1'
            });
          });

          $('#moove_gdpr_cookie_modal .moove-gdpr-tab-main').each( function() {
            var panel = $(this);
            var tab   = this.id ? document.getElementById( 'gdpr-tab-' + this.id ) : null;

            if ( is_tabs && tab ) {
              panel.attr({ 'role': 'tabpanel', 'tabindex': '0', 'aria-labelledby': tab.id });
            } else {
              panel.removeAttr('role tabindex aria-labelledby');
            }

            var toggle = panel.children('.tab-title').children('.gdpr-tab-title-text');
            var body   = panel.children('.moove-gdpr-tab-main-content');

            if ( is_accordion && this.id !== 'privacy_overview' && toggle.length > 0 && body.length > 0 ) {
              if ( ! body.attr('id') ) {
                body.attr( 'id', this.id + '-content' );
              }
              toggle.attr({
                'role': 'button',
                'tabindex': '0',
                'aria-controls': body.attr('id'),
                'aria-expanded': body.is(':visible') ? 'true' : 'false'
              });
            } else {
              toggle.removeAttr('role tabindex aria-controls aria-expanded');
            }
          });

          moove_gdpr_sync_disabled_notes();
          moove_gdpr_ensure_status_region();
        }

        if ( gdpr_narrow_viewport ) {
          var gdpr_on_viewport_change = function() {
            if ( is_gdpr_lightbox ) {
              moove_gdpr_sync_modal_semantics();
            }
          };
          if ( typeof gdpr_narrow_viewport.addEventListener === 'function' ) {
            gdpr_narrow_viewport.addEventListener( 'change', gdpr_on_viewport_change );
          } else if ( typeof gdpr_narrow_viewport.addListener === 'function' ) {
            gdpr_narrow_viewport.addListener( gdpr_on_viewport_change );
          }
        }

        $(document).on('keydown', function(e) {
          if ( e.keyCode !== 9 ) {
            return;
          }

          if ( $('body').hasClass('moove_gdpr_overflow') ) {
            var gdpr_modal = document.getElementById('moove_gdpr_cookie_modal');
            if ( gdpr_modal ) {
              gdpr_trap_focus( e, gdpr_modal );
            }
          } else if ( $('body').hasClass('gdpr-infobar-visible') && moove_gdpr_infobar_dialog() ) {
            // The full-screen banner covers and blocks the page, so it behaves as a modal dialog.
            gdpr_trap_focus( e, moove_gdpr_infobar_dialog() );
          }
        });

        // Tab list: arrow keys move between tabs and select them, Home/End jump to the ends.
        $(document).on('keydown', '#moove-gdpr-menu .moove-gdpr-tab-nav', function(e) {
          var steps  = { 37: -1, 38: -1, 39: 1, 40: 1 };
          var tabs   = $('#moove-gdpr-menu .moove-gdpr-tab-nav:visible');
          var index  = tabs.index( this );
          var target = -1;

          if ( e.altKey || e.ctrlKey || e.metaKey || index < 0 ) {
            return;
          }

          if ( typeof steps[ e.keyCode ] !== 'undefined' ) {
            target = ( index + steps[ e.keyCode ] + tabs.length ) % tabs.length;
          } else if ( e.keyCode === 36 ) {
            target = 0;
          } else if ( e.keyCode === 35 ) {
            target = tabs.length - 1;
          }

          if ( target < 0 ) {
            return;
          }

          e.preventDefault();
          tabs.eq( target ).trigger('focus').trigger('click');
        });

        // Non-native elements acting as buttons (section toggles on narrow screens, add-on
        // "Show details", legacy span-based settings links) get the keys a real button has.
        $(document).on('keydown', '#moove_gdpr_cookie_modal [role="button"], #moove_gdpr_cookie_info_bar [role="button"], #moove_gdpr_cookie_info_bar span.change-settings-button', function(e) {
          if ( e.target !== this || ( e.keyCode !== 13 && e.keyCode !== 32 ) || $(this).is('button, a[href], input') ) {
            return;
          }
          e.preventDefault();
          $(this).trigger('click');
        });

        $(document).on('gdpr_lightbox:close', function(event, instance) {
           $(document).moove_gdpr_lightbox_close();
        });
        $.fn.moove_gdpr_lightbox_close = function(options){
          if ( is_gdpr_lightbox ) {
            $('body').removeClass('moove_gdpr_overflow');
            is_gdpr_lightbox = false;

            // Hand focus back to whatever opened the modal. Without this, focus is lost to
            // <body> and keyboard users restart from the top of the document.
            if ( gdpr_modal_return_focus && document.body.contains( gdpr_modal_return_focus ) ) {
              try {
                gdpr_modal_return_focus.focus();
              } catch (error) {
                gdpr_cc_log( error );
              }
            }
            gdpr_modal_return_focus = null;
          }
        }
        $.fn.moove_gdpr_lightbox_open = function(options){
          if ( is_gdpr_lightbox ) {
            $('body').addClass('moove_gdpr_overflow');
            var cookies = moove_gdpr_read_cookie('moove_gdpr_popup');

            // Remember the trigger, then move focus into the dialog so it is announced and
            // Tab starts inside the modal rather than at the document top.
            // The dialog is shown by class toggle, so `autofocus` never fires here.
            if ( document.activeElement && document.activeElement !== document.body ) {
              gdpr_modal_return_focus = document.activeElement;
              document.activeElement.blur();
            }

            moove_gdpr_sync_modal_semantics();

            var $gdpr_modal = $('#moove_gdpr_cookie_modal');
            if ( $gdpr_modal.length > 0 ) {
              try {
                $gdpr_modal[0].focus();
              } catch (error) {
                gdpr_cc_log( error );
              }
            }

            if ( moove_frontend_gdpr_scripts.show_icons === 'none' ) {
              $('body').addClass('gdpr-no-icons');
            }
            $('.moove-gdpr-status-bar input[type=checkbox]').each(function(){
              if ( ! $(this).is(':checked') ) {
                $(this).closest('.moove-gdpr-tab-main').find('.moove-gdpr-strict-warning-message').slideDown();
              } else {
                $(this).closest('.moove-gdpr-tab-main').find('.moove-gdpr-strict-warning-message').slideUp();
              }
            });


            if ( cookies ) {
              cookies = JSON.parse(cookies);

              moove_gdpr_change_switchers(cookies);
            } else {
              if ( ! $('#moove_gdpr_strict_cookies').is(':checked') ) {
                $('#advanced-cookies .gdpr-cc-form-fieldset').addClass( 'fl-disabled' );
                $('#third_party_cookies .gdpr-cc-form-fieldset').addClass( 'fl-disabled' );
              }
            }
            if ( typeof moove_frontend_gdpr_scripts.hide_save_btn !== 'undefined' && moove_frontend_gdpr_scripts.hide_save_btn === 'true' ) {
              $('.moove-gdpr-modal-save-settings').removeClass('button-visible').hide();
            } else {
              $('.moove-gdpr-modal-save-settings').addClass('button-visible').show();
            }

            
            check_allow_button();
          }
        };

        $(document).on('gdpr_lightbox:open', function(event, instance) {
           $(document).moove_gdpr_lightbox_open();
        });

        $(document).on('click tap','.fl-disabled',function(e){
          if ( $('#moove_gdpr_cookie_modal .moove-gdpr-modal-content').is('.moove_gdpr_modal_theme_v2') ) {
            if ( $('#moove_gdpr_strict_cookies').length > 0 ) {
              $('#moove_gdpr_strict_cookies').trigger('click');
              $(this).trigger('click');
            }
          } else {
            $(this).closest('.moove-gdpr-tab-main-content').find('.moove-gdpr-strict-secondary-warning-message').slideDown();
            var disabled_note = document.getElementById('moove_gdpr_disabled_note');
            if ( disabled_note ) {
              moove_gdpr_announce( disabled_note.textContent );
            }
          }
        });

        $(document).on('change','.moove-gdpr-status-bar input[type=checkbox]',function(e){
          $('.moove-gdpr-modal-save-settings').addClass('button-visible').show();
          var box_id = $(this).closest('.moove-gdpr-tab-main').attr('id');
          $(this).closest('.moove-gdpr-status-bar').toggleClass('checkbox-selected');
          $(this).closest('.moove-gdpr-tab-main').toggleClass('checkbox-selected');
          $('#moove-gdpr-menu .menu-item-' + box_id).toggleClass('menu-item-off');

          if ( ! $(this).is(':checked') ) {
            $(this).closest('.moove-gdpr-tab-main').find('.moove-gdpr-strict-warning-message').slideDown();
          } else {
            $(this).closest('.moove-gdpr-tab-main').find('.moove-gdpr-strict-warning-message').slideUp();
          }
          if ( $(this).is('#moove_gdpr_strict_cookies') ) {

            if ( $(this).is(':checked') ) {
              $('#third_party_cookies fieldset, #third_party_cookies .gdpr-cc-form-fieldset').removeClass('fl-disabled');
              $('#moove_gdpr_performance_cookies').prop('disabled',false);
              
              $('#third_party_cookies .moove-gdpr-strict-secondary-warning-message').slideUp();

              $('#advanced-cookies fieldset, #advanced-cookies .gdpr-cc-form-fieldset').removeClass('fl-disabled');
              $('#advanced-cookies .moove-gdpr-strict-secondary-warning-message').slideUp();

              $('#moove_gdpr_advanced_cookies').prop('disabled',false);

              $('#performance-cookies fieldset, #performance-cookies .gdpr-cc-form-fieldset').removeClass('fl-disabled');
              $('#performance-cookies .moove-gdpr-strict-secondary-warning-message').slideUp();

              $('#moove_gdpr_performance_cc_cookies').prop('disabled',false);

              $('#preference-cookies fieldset, #preference-cookies .gdpr-cc-form-fieldset').removeClass('fl-disabled');
              $('#preference-cookies .moove-gdpr-strict-secondary-warning-message').slideUp();

              $('#moove_gdpr_preference_cc_cookies').prop('disabled',false);
              

            } else {

              $('.gdpr_cookie_settings_shortcode_content').find('input').each(function(){
                $(this).prop('checked',false);
              });

              $('#third_party_cookies fieldset, #third_party_cookies .gdpr-cc-form-fieldset').addClass('fl-disabled').closest('.moove-gdpr-status-bar').removeClass('checkbox-selected');
              $('#moove_gdpr_performance_cookies').prop('disabled',true).prop('checked',false);
              
              $('#advanced-cookies fieldset, #advanced-cookies .gdpr-cc-form-fieldset').addClass('fl-disabled').closest('.moove-gdpr-status-bar').removeClass('checkbox-selected');
              
              $('#moove_gdpr_advanced_cookies').prop('disabled',true).prop('checked',false);

              $('#performance-cookies fieldset, #performance-cookies .gdpr-cc-form-fieldset').addClass('fl-disabled').closest('.moove-gdpr-status-bar').removeClass('checkbox-selected');
              
              $('#moove_gdpr_performance_cc_cookies').prop('disabled',true).prop('checked',false);

              $('#preference-cookies fieldset, #preference-cookies .gdpr-cc-form-fieldset').addClass('fl-disabled').closest('.moove-gdpr-status-bar').removeClass('checkbox-selected');
              
              $('#moove_gdpr_preference_cc_cookies').prop('disabled',true).prop('checked',false);

            }
          }

          $('input[data-name="'+$(this).attr('name')+'"]').prop('checked',$(this).is(':checked'));

          check_allow_button();
          moove_gdpr_sync_disabled_notes();
        });

        $(document).on('click tap','.gdpr_cookie_settings_shortcode_content .gdpr-shr-save-settings',function(e){
          e.preventDefault();
          save_cookies( true );
          $('.gdpr_lightbox .gdpr_lightbox-close').trigger('click');
          $(document).moove_gdpr_lightbox_close();
          moove_gdpr_check_reload( 'modal-save-settings' );
        })
        $(document).on('change','.gdpr_cookie_settings_shortcode_content input[type=checkbox]',function(e){
          var target = $(this).attr('data-name');
          var t_cb = $('#'+target);
          if ( $(this).is(':checked') ) {
            $('input[data-name="'+target+'"]').prop('checked',true);           
            if ( $(this).attr('data-name') !== 'moove_gdpr_strict_cookies' ) {
              if ( ! $(this).closest('.gdpr_cookie_settings_shortcode_content').find('input[data-name="moove_gdpr_strict_cookies"]').is(':checked') ) {
                $('input[data-name="'+target+'"]').prop('checked',false);
                $('.gdpr_cookie_settings_shortcode_content input[data-name="moove_gdpr_strict_cookies"]').closest('.gdpr-shr-switch').css('transform', 'scale(1.2)');
                setTimeout(function(){
                  $('.gdpr_cookie_settings_shortcode_content input[data-name="moove_gdpr_strict_cookies"]').closest('.gdpr-shr-switch').css('transform', 'scale(1)');
                },300);
              }
            }
          } else {  
            $('input[data-name="'+target+'"]').prop('checked',$(this).is(':checked'));
            if ( $(this).attr('data-name') === 'moove_gdpr_strict_cookies' ) {
              $('.gdpr_cookie_settings_shortcode_content').find('input[type="checkbox"]').prop('checked',false);
            }
          }
          t_cb.trigger('click');
        });


        $(document).on('click tap','.moove-gdpr-modal-allow-all, [href*="#gdpr-accept-cookies"]',function(e){
          e.preventDefault();
          $('#moove_gdpr_cookie_modal').find('input[type=checkbox]').each(function(){
            var checkbox = $(this);
            if ( ! checkbox.is(':checked') ) {
              checkbox.trigger('click');
            }            
          });
          moove_gdpr_save_cookies( 'enable_all enable-all-button' );
          $('.gdpr_lightbox .gdpr_lightbox-close').trigger('click');
          moove_gdpr_hide_infobar();
          save_cookies( false );
          $(document).moove_gdpr_lightbox_close();
        });

        $(document).on('click tap','.moove-gdpr-infobar-allow-all',function(e){
          e.preventDefault();
          $('#moove_gdpr_cookie_modal').find('input[type=checkbox]').each(function(){
            var checkbox = $(this);
            if ( ! checkbox.is(':checked') ) {
              checkbox.trigger('click');
            }            
          });
          moove_gdpr_save_cookies( 'enable_all allow-btn' );
          $('.gdpr_lightbox .gdpr_lightbox-close').trigger('click');
          moove_gdpr_hide_infobar();
          save_cookies( false );          
        });

        $(document).on('click tap','.moove-gdpr-modal-save-settings',function(e){
          e.preventDefault();          
          save_cookies( true );
          $('.gdpr_lightbox .gdpr_lightbox-close').trigger('click');
          $(document).moove_gdpr_lightbox_close();
          moove_gdpr_check_reload( 'modal-save-settings' );

        });

        var delete_cookie = function(name) {
          try {
            $(document).find('script[data-gdpr]').each(function() {
              gdpr_cc_log( 'script_removed: ' + $(this).attr('src') );
              $(this).remove();
            });
            if ( ! name.includes('woocommerce') && ! name.includes('wc_') && ! name.includes('moove_gdpr_popup') && ! name.includes('wordpress') ) {
              document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;SameSite=Lax';
            }
          } catch(e) {
            gdpr_cc_log( 'error in delete_cookie: ' + e );
          }
        };

        function gdpr_delete_all_cookies( type ) {
          try {
            $(document).find('script[data-gdpr]').each(function() {
              gdpr_cc_log( 'script_removed: ' + $(this).attr('src') );
              $(this).remove();
            });
            var cookies = document.cookie.split(";");
            var host    = window.location.hostname.toLowerCase();
            for (var i = 0; i < cookies.length; i++) {
              var cookie = cookies[i];
              var eqPos = cookie.indexOf("=");
              var name = ( eqPos > -1 ? cookie.substr(0, eqPos) : cookie ).trim();
              // wp_consent_* holds the visitor's consent state for the WP Consent API.
              // Wiping it leaves consent-aware plugins with no value at all, which reads as
              // "never asked" rather than "denied", so they stay blocked with no way back.
              if ( name && ! name.includes('woocommerce') && ! name.includes('wc_') && ! name.includes('moove_gdpr_popup') && ! name.includes('wordpress') && ! name.includes('wp_consent') ) {
                // path=/ is required: without it the browser scopes the delete to the current
                // page's directory and misses cookies set on /.
                var expired = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
                var domains = gdpr_get_cookie_domains( name, host );
                document.cookie = expired;
                for (var j = 0; j < domains.length; j++) {
                  document.cookie = expired + ";domain=." + domains[j];
                }
                gdpr_cc_log('cookie removed: ' + name + ' - ' + domains.join(', '));
              }
            }
          } catch(e) {
            gdpr_cc_log( 'error in gdpr_delete_all_cookies: ' + e );
          }
          if ( typeof( sessionStorage ) !== "undefined" ) {
            sessionStorage.removeItem("gdpr_session");
          }
        }

        function save_cookies( _delete_cookies ) {
          var cookies = moove_gdpr_read_cookie('moove_gdpr_popup');

          if ( _delete_cookies ) {
            gdpr_delete_all_cookies();
            gdpr_ajax_php_delete_cookies();
          }

          var strict      = '0';
          var advanced    = '0';
          var thirdparty  = '0';
          var performance  = '0';
          var preference  = '0';
          
          var has_checked = false;

          if ( cookies ) {
            cookies       = JSON.parse( cookies );
            strict        = cookies.strict;
            advanced      = cookies.advanced;
            thirdparty    = cookies.thirdparty;
            performance   = typeof cookies.performance !== 'undefined' ? cookies.performance : 0;
            preference    = typeof cookies.preference !== 'undefined' ? cookies.preference : 0;
          }

          if ( $(document).find('#moove_gdpr_strict_cookies').length > 0 ) {
            // STRICT PARTY COOKIES
            if ( $(document).find('#moove_gdpr_strict_cookies').is(':checked') ) {
              strict = '1';
              has_checked = true;
            } else {
              strict = '0';
            }
          } else {
            has_checked = true;
            strict = '1';
          }

          // THIRD PARTY COOKIES
          if ( $(document).find('#moove_gdpr_performance_cookies').is(':checked') ) {
            thirdparty = '1';
            has_checked = true;
          } else {
            thirdparty = '0';
          }

          // ADVANCED PARTY COOKIES
          if ( $(document).find('#moove_gdpr_advanced_cookies').is(':checked') ) {
            advanced = '1';
            has_checked = true;
          } else {
            advanced = '0';
          }

          // PERFORMANCE PARTY COOKIES
          if ( $(document).find('#moove_gdpr_performance_cc_cookies').is(':checked') ) {
            performance = '1';
            has_checked = true;
          } else {
            performance = '0';
          }

          // PREFERENCE PARTY COOKIES
          if ( $(document).find('#moove_gdpr_preference_cc_cookies').is(':checked') ) {
            preference = '1';
            has_checked = true;
          } else {
            preference = '0';
          }

          if ( ! cookies && has_checked ) {
            moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: strict, thirdparty: thirdparty, advanced: advanced, performance: performance, preference: preference}),cookie_expiration);
            moove_gdpr_hide_infobar();
            $(document).find('#moove_gdpr_save_popup_settings_button').show();
          } else {
            if ( cookies ) {
              if ( ! consent_log_all ) {
                moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: strict, thirdparty: thirdparty, advanced: advanced, performance: performance, preference: preference}),cookie_expiration);
              }
            }
          }
          var cookies = moove_gdpr_read_cookie('moove_gdpr_popup');

          if ( cookies ) {
            cookies = JSON.parse( cookies );

            if ( cookies.strict == '0' && cookies.thirdparty == '0' && cookies.advanced == '0' && cookies.performance == '0'  && cookies.preference == '0' ) {
              gdpr_delete_all_cookies();
            }
          }
        }

        
        if(window.location.hash) {
          var hash = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
          hash = hash.replace(/\/$/, '');
          if ( hash === 'moove_gdpr_cookie_modal' || hash === 'gdpr_cookie_modal' ) {
            is_gdpr_lightbox = true;
            gdpr_save_analytics( 'opened_modal_from_link', '' );
            setTimeout(function(){
              if ( $('#moove_gdpr_cookie_modal').length > 0 ) {
                modal_instance = gdpr_lightbox('#moove_gdpr_cookie_modal');
                // $('#moove_gdpr_strict_cookies').trigger('click').trigger('click');
                $('.gdpr_lightbox').addClass('moove_gdpr_cookie_modal_open');
                $(document).moove_gdpr_lightbox_open();
              }
            }, 500);
          }

          if ( hash === 'gdpr-accept-cookies' ) {
            $('#moove_gdpr_cookie_modal').find('input[type=checkbox]').each(function(){
              var checkbox = $(this);
              if ( ! checkbox.is(':checked') ) {
                checkbox.trigger('click');
              }            
            });
            moove_gdpr_save_cookies( 'enable_all enable-all-button' );
            $('.gdpr_lightbox .gdpr_lightbox-close').trigger('click');
            moove_gdpr_hide_infobar();
            save_cookies( true );
            $(document).moove_gdpr_lightbox_close();
          }

          if ( hash === 'gdpr-reject-cookies' ) {
            gdpr_delete_all_cookies();
            gdpr_ajax_delete_cookies();

            if ( $('#moove_gdpr_cookie_info_bar').length > 0 ) {
              $('#moove_gdpr_cookie_info_bar').addClass('moove-gdpr-info-bar-hidden');
              $('body').removeClass('gdpr-infobar-visible');
              $('#moove_gdpr_cookie_info_bar').hide();
              $('#moove_gdpr_save_popup_settings_button').show();
            }
            moove_gdpr_show_infobar();
            moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: '1', thirdparty: '0', advanced: '0', performance: '0', preference: '0' }),cookie_expiration);
            setTimeout(function(){
              moove_gdpr_create_cookie('moove_gdpr_popup',JSON.stringify({strict: '1', thirdparty: '0', advanced: '0', performance: '0', preference: '0'}),cookie_expiration);
            }, 500);
          }
        }
  

      },
      finalize: function() {
        // JavaScript to be fired on all pages, after page specific JS is fired
      }
    }
  };

  // The routing fires all common scripts, followed by the page specific scripts.
  // Add additional events for more control over timing e.g. a finalize event
  var GDPR_UTIL_FE = {
    fire: function(func, funcname, args) {
      var fire;
      var namespace = GDPR_FE;
      funcname = (funcname === undefined) ? 'init' : funcname;
      fire = func !== '';
      fire = fire && namespace[func];
      fire = fire && typeof namespace[func][funcname] === 'function';

      if (fire) {
        namespace[func][funcname](args);
      }
    },
    loadEvents: function() {
      // Fire common init JS
      var gdpr_js_init  = false;
      var gpc_blocked   = false;
      if ( typeof moove_frontend_gdpr_scripts.gpc !== 'undefined' && parseInt( moove_frontend_gdpr_scripts.gpc ) === 1 ) {
        if ( typeof navigator.globalPrivacyControl !== 'undefined' ) {
          gpcValue = navigator.globalPrivacyControl;
          if ( gpcValue ) {
            gpc_blocked = true;
            console.warn('GDPR Cookie Compliance - Blocked by Global Policy Control (GPC)');
          }
        }
      }

      if ( ! gpc_blocked ) {
        if ( typeof moove_frontend_gdpr_scripts.geo_location !== 'undefined' && moove_frontend_gdpr_scripts.geo_location === 'true' ) {
          var gdpr_geo_cache_name = 'moove_gdpr_geo_cache';
          // Bump when the stored decision changes meaning, so that decisions
          // cached by an earlier version are fetched again. The Consent Mode and
          // Clarity snippets read this cookie too and check the same version: see
          // gdpr_consent_cookie_js_preamble() in class-moove-gdpr-content.php.
          // 3: an unknown location is stored too, flagged as unresolved.
          var gdpr_geo_cache_version = 3;

          var gdpr_geo_cache_read = function() {
            var nameEQ = encodeURIComponent( gdpr_geo_cache_name ) + '=';
            var ca = document.cookie.split(';');
            for ( var i = 0; i < ca.length; i++ ) {
              var c = ca[i];
              while ( c.charAt(0) === ' ' ) { c = c.substring(1, c.length); }
              if ( c.indexOf( nameEQ ) === 0 ) {
                try {
                  return JSON.parse( decodeURIComponent( c.substring( nameEQ.length, c.length ) ) );
                } catch(e) { return null; }
              }
            }
            return null;
          };

          var gdpr_geo_cache_write = function( data, unresolved ) {
            // Session cookie (no expires) — geo result is per-session, avoids stale risk.
            // Only the decision is stored; the full response can outgrow the cookie size limit.
            var stored = {
              v: gdpr_geo_cache_version,
              display_cookie_banner: data.display_cookie_banner,
              enabled_default: data.enabled_default
            };
            // An unknown location is kept as well, so that the Consent Mode snippet starts
            // the next pages denied, as the banner is shown, instead of applying the
            // region-based default again. It is not reused as an answer: the next page
            // asks for the location again.
            if ( unresolved ) {
              stored.unresolved = 1;
            }
            document.cookie = encodeURIComponent( gdpr_geo_cache_name ) + '=' + encodeURIComponent( JSON.stringify( stored ) ) + '; path=/; SameSite=Lax';
          };

          var gdpr_geo_apply = function( data ) {
            if ( typeof data.display_cookie_banner !== 'undefined' ) {
              moove_frontend_gdpr_scripts.display_cookie_banner = data.display_cookie_banner;
            }
            if ( typeof data.enabled_default !== 'undefined' ) {
              moove_frontend_gdpr_scripts.enabled_default = data.enabled_default;
            }
          };

          var gdpr_geo_init = function() {
            if ( ! gdpr_js_init ) {
              gdpr_js_init = true;
              // Consent Mode's region-specific defaults can grant before the location is
              // known (see gdpr_consent_regions() in class-moove-gdpr-content.php). They
              // are withdrawn when the banner is shown after all.
              if ( moove_frontend_gdpr_scripts.display_cookie_banner !== 'false' && typeof window.gdpr_cc_gtm2_geo_banner === 'function' ) {
                window.gdpr_cc_gtm2_geo_banner();
                gdpr_expire_google_cookies();
              }
              GDPR_UTIL_FE.fire('common');
            }
          };

          // No answer means the visitor's location is unknown, so the banner
          // is shown. Hiding it would load every script category.
          var gdpr_geo_fail_closed = function() {
            moove_frontend_gdpr_scripts.display_cookie_banner = 'true';
            gdpr_geo_cache_write( { display_cookie_banner: 'true' }, true );
            gdpr_geo_init();
          };

          var geoCached = gdpr_geo_cache_read();
          if ( geoCached !== null && geoCached.v === gdpr_geo_cache_version && ! geoCached.unresolved ) {
            gdpr_geo_apply( geoCached );
            gdpr_geo_init();
          } else {
            jQuery.ajax({
              type: 'POST',
              url: moove_frontend_gdpr_scripts.ajaxurl,
              data: {
                action: 'moove_gdpr_localize_scripts',
              },
              timeout: 10000,
              success: function( msg ) {
                var object = null;
                try {
                  object = typeof msg === 'string' ? JSON.parse( msg ) : msg;
                } catch(e) {
                  object = null;
                }
                if ( object === null || typeof object !== 'object' ) {
                  gdpr_geo_fail_closed();
                  return;
                }
                gdpr_geo_apply( object );
                // An unresolved location is asked for again on the next page view.
                gdpr_geo_cache_write( object, object.geo_resolved === 'false' );
                gdpr_geo_init();
              },
              error: function() {
                gdpr_geo_fail_closed();
              }
            });
          }
        } else {
          var gdpr_script_delay = typeof moove_frontend_gdpr_scripts.script_delay !== undefined && parseInt( moove_frontend_gdpr_scripts.script_delay ) >= 0 ? parseInt( moove_frontend_gdpr_scripts.script_delay ) : 0;
          if ( gdpr_script_delay > 0 ) {
            setTimeout( function(){
              GDPR_UTIL_FE.fire('common');
            }, gdpr_script_delay );
          } else {
            GDPR_UTIL_FE.fire('common');
          }
        }
      }
      
      // Fire page-specific init JS, and then finalize JS
      $.each(document.body.className.replace(/-/g, '_').split(/\s+/), function(i, classnm) {
        GDPR_UTIL_FE.fire(classnm);
        GDPR_UTIL_FE.fire(classnm, 'finalize');
      });

      // Fire common finalize JS
      GDPR_UTIL_FE.fire('common', 'finalize');
    }
  };

  // Load Events
  $(document).ready(GDPR_UTIL_FE.loadEvents);

})(jQuery); // Fully reference jQuery after this point.
