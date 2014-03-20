( function( $ ) {
	$( document ).ready(function() {
		// Initialize BWS Google Maps plugin
		$( '#gglmps_editor_map' ).bws_googlemaps({
			'mapType'                   : $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() || 'roadmap',
			'tilt45'                    : $( '#gglmps_basic_tilt45' ).is( ':checked' ) ? true : false,
			'autoZoom'                  : $( '#gglmps_basic_auto_zoom' ).is( ':checked' ) ? true : false,
			'zoom'                      : Number( $( '#gglmps_basic_zoom' ).val() ),
			'mapTypeControl'            : $( '#gglmps_control_map_type' ).is( ':checked' ) ? true : false,
			'panControl'                : $( '#gglmps_control_pan' ).is( ':checked' ) ? true : false,
			'rotateControl'             : $( '#gglmps_control_rotate' ).is( ':checked' ) ? true : false,
			'zoomControl'               : $( '#gglmps_control_zoom' ).is( ':checked' ) ? true : false,
			'scaleControl'              : $( '#gglmps_control_scale' ).is( ':checked' ) ? true : false,
			'streetViewControl'         : $( '#gglmps_control_street_view' ).is( ':checked' ) ? true : false,
			'overviewMapControl'        : $( '#gglmps_control_overview_map' ).is( ':checked' ) ? true : false,
			'overviewMapControlOptions' : { opened : $( '#gglmps_control_overview_map_opened' ).is( ':checked' ) ? true : false },
			'draggable'                 : $( '#gglmps_control_map_draggable' ).is( ':checked' ) ? true : false,
			'disableDoubleClickZoom'    : $( '#gglmps_control_double_click' ).is( ':checked' ) ? true : false,
			'scrollwheel'               : $( '#gglmps_control_scroll_wheel' ).is( ':checked' ) ? true : false,
			'onZoomChange'              : function ( value ) {
				$( '#gglmps_zoom_slider' ).slider({
					value : value
				});
			},
			'onMapTypeChange'           : function( type ) {
				if ( $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() != type ) {
					$( '#gglmps_basic_map_type' ).find( 'option[value=' + type + ']' ).attr( 'selected', true ).trigger( 'change' );
					$( '#gglmps_zoom_slider' ).slider({
						max : $( '#gglmps_editor_map' ).bws_googlemaps( 'getMaxZoom', type )
					});
				}
			},
			'onGetCoordinates'          : function( lat, lng ) {
				$( '#gglmps_marker_location' ).val( lat.toFixed( 6 ) + ',' + lng.toFixed( 6 ) ).trigger( 'input' );
			}
		});

		// Add saved markers to preview map
		$( '#gglmps_editor_map' ).bws_googlemaps( 'addMarker', new function() {
			var markers = [];
			$( '#gglmps_markers_container .gglmps_marker' ).each( function( index ) {
				markers.push({
					'latlng'   : $( this ).find( '.gglmps_input_latlng' ).val(),
					'location' : $( this ).find( '.gglmps_textarea_location' ).text(),
					'tooltip'  : $( this ).find( '.gglmps_textarea_tooltip' ).text()
				});
			});
			return markers;
		});

		// Add support Google autocomplete
		$( '#gglmps_marker_location' ).autocomplete({
			'onGetResult' : function( lat, lng ) {
				$( '#gglmps_marker_latlng' ).val( lat.toFixed( 6 ) + ',' + lng.toFixed( 6 ) );
				$( '#gglmps_marker_location' ).removeClass( 'gglmps_editor_error' );
			}
		}).on( 'input paste', function() {
			var isCoordinates = $( this ).val().search( /^[-]?[\d]{1,2}[.][\d]{3,9}[,][-]?[\d]{1,3}[.][\d]{3,9}$/ );
			$( '#gglmps_marker_location' ).removeClass( 'gglmps_editor_error' );
			 if ( isCoordinates == 0 ) {
				$( '#gglmps_marker_latlng' ).val( $( this ).val() );
			 } else {
			 	$( '#gglmps_marker_latlng' ).val( '' );
			 }
		}).on( 'keydown', function( e ) {
			if ( e.keyCode == 13 ) {
				return false;
			}
		});
		
		// Adds a marker to the list markers
		$( '#gglmps_marker_add' ).on( 'click', function() {
			var isCoordinates = $( '#gglmps_marker_location' ).val().search( /^[-]?[\d]{1,2}[.][\d]{3,9}[,][-]?[\d]{1,3}[.][\d]{3,9}$/ )
				err = 0;
			if ( $( '#gglmps_marker_location' ).val() == '' || $( '#gglmps_marker_latlng' ).val() == '' ) {
				$( '#gglmps_marker_location' ).addClass( 'gglmps_editor_error' );
				err = 1;
			}
			if ( err == 1 ) {
				return;
			}
			var geocoder = new google.maps.Geocoder(),
				lat = $( '#gglmps_marker_latlng' ).val().split(',')[0],
				lng = $( '#gglmps_marker_latlng' ).val().split(',')[1];
			geocoder.geocode({ 'latLng' : new google.maps.LatLng( lat, lng ) }, function( results, status ) {
				if ( results[0] && isCoordinates == 0 ) {
					$( '#gglmps_marker_location' ).val( results[0]['formatted_address'] );
				}
				var $marker = '\
					<li class="gglmps_marker">\
						<div class="gglmps_marker_control">\
							<span class="gglmps_marker_delete">{delete}</span>\
							<span class="gglmps_marker_edit">{edit}</span>\
							<span class="gglmps_marker_find">{find}</span>\
							<span class="gglmps_marker_latlng">[{gglmps_latlang}]</span>\
						</div>\
						<div class="gglmps_marker_data">\
							<div class="gglmps_marker_location">{gglmps_location}</div>\
							<xmp class="gglmps_marker_tooltip">{gglmps_tooltip}</xmp>\
							<input class="gglmps_input_latlng" name="gglmps_list_marker_latlng[]" type="hidden" value="{gglmps_latlang}">\
							<textarea class="gglmps_textarea_location" name="gglmps_list_marker_location[]">{gglmps_location}</textarea>\
							<textarea class="gglmps_textarea_tooltip" name="gglmps_list_marker_tooltip[]">{gglmps_tooltip}</textarea>\
						</div>\
					</li>\
				';
				$marker = $marker.replace( /{gglmps_latlang}/g, $( '#gglmps_marker_latlng' ).val() );
				$marker = $marker.replace( /{gglmps_location}/g, $( '#gglmps_marker_location' ).val() );
				$marker = $marker.replace( /{gglmps_tooltip}/g, $( '#gglmps_marker_tooltip' ).val() );
				$marker = $marker.replace( /{delete}/g, gglmps_translation.deleteMarker );
				$marker = $marker.replace( /{edit}/g, gglmps_translation.editMarker );
				$marker = $marker.replace( /{find}/g, gglmps_translation.findMarker );
				$( '#gglmps_markers_container' ).append( $marker );
				$( '#gglmps_editor_map' ).bws_googlemaps( 'addMarker', {
					'latlng'   : $( '#gglmps_marker_latlng' ).val(),
					'location' : $( '#gglmps_marker_location' ).val(),
					'tooltip'  : $( '#gglmps_marker_tooltip' ).val()
				});
				$( '#gglmps_marker_cancel' ).hide();
				$( '#gglmps_marker_latlng' ).val( '' );
				$( '#gglmps_marker_location' ).val( '' );
				$( '#gglmps_marker_tooltip' ).val( '' );
				if ( $( '.gglmps_marker' ).size() > 0 ) {
					$( '.gglmps_no_markers' ).remove();
				}
			});
		});

		// Animation marker for visual search on the preview map
		$( '#gglmps_markers_container' ).on( 'click', '.gglmps_marker_find', function() {
			var markerIndex = $( this ).parents( '.gglmps_marker' ).index();
			$( '#gglmps_editor_map' ).bws_googlemaps( 'findMarker', markerIndex );
		});

		// Editing marker
		$( '#gglmps_markers_container' ).on( 'click', '.gglmps_marker_edit', function() {
			var markerIndex = $( this ).parents( '.gglmps_marker' ).index(),
				$marker = $( '#gglmps_markers_container .gglmps_marker' ).eq( markerIndex );
			$( '#gglmps_marker_add' ).hide();
			$( '#gglmps_marker_update' ).data( 'markerIndex', markerIndex ).show();
			$( '#gglmps_marker_cancel' ).show();
			$( '#gglmps_marker_location' ).val( $marker.find( '.gglmps_textarea_location' ).text() );
			$( '#gglmps_marker_latlng' ).val('');
			$( '#gglmps_marker_tooltip' ).val( $marker.find( '.gglmps_textarea_tooltip' ).text() );
			$( '#gglmps_marker_location' ).autocomplete( 'disabled', true );
		});

		// Deleting marker from the list markers
		$( '#gglmps_markers_container' ).on( 'click', '.gglmps_marker_delete', function() {
			var markerIndex = $( this ).parents( '.gglmps_marker' ).index(),
				$marker = $( '#gglmps_markers_container .gglmps_marker' ).eq( markerIndex );
			$( '#gglmps_editor_map' ).bws_googlemaps( 'deleteMarker', markerIndex );
			$marker.remove();
			$( '#gglmps_marker_cancel' ).trigger( 'click' );
			if ( $( '#gglmps_markers_container .gglmps_marker' ).size() == 0 ) {
				$( '#gglmps_markers_container' ).append( '<li class="gglmps_no_markers">' + gglmps_translation.noMarkers + '</li>' );	
			}
		});

		// Cancel editing marker
		$( '#gglmps_marker_cancel' ).on( 'click', function() {
			$( '#gglmps_marker_update' ).data( 'markerIndex', null ).hide();
			$( '#gglmps_marker_cancel' ).hide();
			$( '#gglmps_marker_add' ).show();
			$( '#gglmps_marker_location' ).val( '' );
			$( '#gglmps_marker_tooltip' ).val( '' );
			$( '#gglmps_marker_location' ).autocomplete( 'disabled', false );
		});

		// Update edited marker
		$( '#gglmps_marker_update' ).on( 'click', function() {
			if ( $( '#gglmps_marker_location' ).val() == '' ) {
				$( '#gglmps_marker_location' ).addClass( 'gglmps_editor_error' );
				return;
			}
			var markerIndex = $( this ).data( 'markerIndex' ),
				$marker = $( '#gglmps_markers_container .gglmps_marker' ).eq( markerIndex );
			$marker.find( '.gglmps_marker_location' ).text( $( '#gglmps_marker_location' ).val() );
			$marker.find( '.gglmps_marker_tooltip' ).text( $( '#gglmps_marker_tooltip' ).val() );
			$marker.find( '.gglmps_textarea_location' ).text( $( '#gglmps_marker_location' ).val() );
			$marker.find( '.gglmps_textarea_tooltip' ).text( $( '#gglmps_marker_tooltip' ).val() );
			$( '#gglmps_editor_map' ).bws_googlemaps( 'updateMarker', {
				'index'   : markerIndex,
				'tooltip' : $( '#gglmps_marker_tooltip' ).val()
			});
			$( '#gglmps_marker_update' ).data( 'markerIndex', null ).hide();
			$( '#gglmps_marker_cancel' ).hide();
			$( '#gglmps_marker_add' ).show();
			$( '#gglmps_marker_location' ).val( '' );
			$( '#gglmps_marker_latlng' ).attr( 'disabled', false );
			$( '#gglmps_marker_latlng' ).val( '' );
			$( '#gglmps_marker_tooltip' ).val( '' );
			$( '#gglmps_marker_location' ).autocomplete( 'disabled', false );
		});

		// Resizing width of the preview map
		$( '#gglmps_basic_width' ).on( 'keydown', function() {
			setTimeout(function() {
				$( '#gglmps_editor_preview, #gglmps_editor_preview_wrap' ).animate({
					'width' : $( '#gglmps_basic_width' ).val()
				}, 500 , function() {
					$( '#gglmps_editor_map' ).bws_googlemaps( 'resize' );
				});
			}, 1000 );
		});

		// Resizing height of the preview map
		$( '#gglmps_basic_height' ).on( 'keydown', function() {
			setTimeout(function() {
				$( '#gglmps_editor_preview, #gglmps_editor_preview_wrap' ).animate({
					'height' : $( '#gglmps_basic_height' ).val()
				}, 500, function() {
					$( '#gglmps_editor_map' ).bws_googlemaps( 'resize' );
				});
			}, 1000 );
		});

		// scrolling preview map
		$( window ).scroll(function() {
			if ( $( '#gglmps_editor_preview' ).offset().left > 182 ) {
				$( '#gglmps_editor_preview_wrap' ).css({ 'position' : 'fixed' });
			} else {
				$( '#gglmps_editor_preview_wrap' ).css({ 'position' : 'relative' });
			}
		});

		// Check availability Map View 45°
		if ( $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() == 'roadmap' || $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() == 'terrain' ) {
			$( '#gglmps_basic_tilt45' ).attr( 'disabled', true );
		}

		// Change map type in the preview map and check availability Map View 45° when changed map type
		$( '#gglmps_basic_map_type' ).on( 'change', function() {
			$( '#gglmps_editor_map' ).bws_googlemaps( 'setMapType', $( this ).find( 'option:selected' ).val() );
			if ( $( this ).find( 'option:selected' ).val() == 'satellite' || $( this ).find( 'option:selected' ).val() == 'hybrid' ) {
				$( '#gglmps_basic_tilt45' ).attr( 'disabled', false );
			} else {
				$( '#gglmps_basic_tilt45' ).attr( 'disabled', true );
			}
		});

		// Set support Map View 45° in the preview map
		$( '#gglmps_basic_tilt45' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ){
				case true:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setTilt', 45 );
					break;
				case false:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setTilt', 0 );
					break;
			}
		});

		// Hide zoom slider if auto zoom is checked
		if ( $( '#gglmps_basic_auto_zoom' ).is( ':checked' ) ) {
			$( '#gglmps_zoom_wrap' ).hide();
		}

		// Switching between auto zoom and manual zoom
		$( '#gglmps_basic_auto_zoom' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ) {
				case true:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setAutoZoom', true );
					$( '#gglmps_zoom_wrap' ).hide();
					break;
				case false:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setAutoZoom', false );
					$( '#gglmps_zoom_wrap' ).show();
					break;
			}
		});

		// Zoom slider
		$( '#gglmps_zoom_slider' ).slider({
			value  : $( '#gglmps_basic_zoom' ).val(),
			min    : 0,
			max    : $( '#gglmps_editor_map' ).bws_googlemaps( 'getMaxZoom', $( '#gglmps_basic_map_type' ).find( 'option:selected' ).val() ),
			step   : 1,
			create : function( event, ui ) {
				$( '#gglmps_zoom_value' ).text( '[' + $( this ).slider( 'value' ) + ']' );
				$( '#gglmps_basic_zoom' ).hide();
			},
			slide : function( event, ui ) {
				$( '#gglmps_zoom_value' ).text( '[' + ui.value + ']' );
				$( '#gglmps_editor_map' ).bws_googlemaps( 'setZoom', ui.value );
			},
			change: function( event, ui ) {
				$( '#gglmps_basic_zoom' ).val( ui.value );
				$( '#gglmps_zoom_value' ).text( '[' + ui.value + ']' );
			}
		});

		// Checking visibility additional options on the editor page
		if ( $( '#gglmps_editor_additional_options' ).is( ':checked' ) == false ) {
			$( '.gglmps_editor_additional_options' ).hide();
		}

		// Show or hide additional options on the editor page
		$( '#gglmps_editor_additional_options' ).on( 'click', function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '.gglmps_editor_additional_options' ).show();
			} else {
				$( '.gglmps_editor_additional_options' ).hide();
			}
		});

		// Show or hide map type control in the preview map
		$( '#gglmps_control_map_type' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ) {
				case true:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setMapTypeControl', true );
					break;
				case false:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setMapTypeControl', false );
					break;
			}
		});

		// Show or hide pan control in the preview map
		$( '#gglmps_control_pan' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ) {
				case true:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setPanControl', true );
					break;
				case false:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setPanControl', false );
					break;
			}
		});

		// Show or hide rotate control in the preview map
		$( '#gglmps_control_rotate' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ) {
				case true:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setRotateControl', true );
					break;
				case false:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setRotateControl', false );
					break;
			}
		});

		// Show or hide zoom control in the preview map
		$( '#gglmps_control_zoom' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ){
				case true:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setZoomControl', true );
					break;
				case false:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setZoomControl', false );
					break;
			}
		});

		// Show or hide scale control in the preview map
		$( '#gglmps_control_scale' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ) {
				case true:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setScaleControl', true );
					break;
				case false:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setScaleControl', false );
					break;
			}
		});

		// Show or hide street view control in the preview map
		$( '#gglmps_control_street_view' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ) {
				case true:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setStreetViewControl', true );
					break;
				case false:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setStreetViewControl', false );
					break;
			}
		});

		// Show or hide overview map control in the preview map
		$( '#gglmps_control_overview_map' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ) {
				case true:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setOverviewMapControl', true );
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setOverviewMapControlOpened', $( '#gglmps_control_overview_map_opened' ).is( ':checked' ) ? true : false );
					break;
				case false:
					$( '#gglmps_control_overview_map_opened' ).attr( 'checked', false );
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setOverviewMapControl', false );
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setOverviewMapControlOpened', false );
					break;
			}
		});

		// Open overview map control in the preview map
		$( '#gglmps_control_overview_map_opened' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ) {
				case true:
					$( '#gglmps_control_overview_map' ).attr( 'checked', true );
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setOverviewMapControl', true );
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setOverviewMapControlOpened', true );
					break;
				case false:
					$( '#gglmps_control_overview_map' ).attr( 'checked', false );
					$( '#gglmps_control_overview_map_opened' ).attr( 'checked', false );
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setOverviewMapControl', false );
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setOverviewMapControlOpened', false );
					break;
			}
		});

		// Check availability draggable preview map
		$( '#gglmps_control_map_draggable' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ) {
				case true:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setDraggable', true );
					break;
				case false:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setDraggable', false );
					break;
			}
		});

		// Check availability double click map in the preview map
		$( '#gglmps_control_double_click' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ) {
				case true:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setDoubleClick', true );
					break;
				case false:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setDoubleClick', false );
					break;
			}
		});

		// Check availability scroll wheel in the preview map
		$( '#gglmps_control_scroll_wheel' ).on( 'change', function() {
			switch ( $( this ).is( ':checked' ) ) {
				case true:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setScrollWheel', true );
					break;
				case false:
					$( '#gglmps_editor_map' ).bws_googlemaps( 'setScrollWheel', false );
					break;
			}
		});

		// Save new or update existing map
		$( '#gglmps_editor_form' ).on( 'submit', function() {
			var err = 0;
			$( '#gglmps_basic_width, #gglmps_basic_height' ).each( function() {
				if ( $( this ).val() == '' ) {
					$( this ).addClass( 'gglmps_editor_error' );
					$( this ).on( 'keydown', function() {
						$( this ).removeClass( 'gglmps_editor_error' );
					});
					err = 1;
				}
			});
			if ( err == 1 ) {
				$( 'body, html' ).animate( { scrollTop : $( '#gglmps_map_title' ).offset().top - 50 }, 'fast' );
				return false;
			}
			if ( $( '#gglmps_basic_width' ).val() < 150 ) {
				$( '#gglmps_basic_width' ).val( 150 );
			}
			if ( $( '#gglmps_basic_height' ).val() < 150 ) {
				$( '#gglmps_basic_height' ).val( 150 );
			}
			if ( $( '#gglmps_basic_zoom' ).val() < 0 ) {
				$( '#gglmps_basic_zoom' ).val( 0 );
			}
			if ( $( '#gglmps_basic_zoom' ).val() > 21 ) {
				$( '#gglmps_basic_zoom' ).val( 21 );
			}
		});
	}); // end document ready

})( jQuery );

// Autocomplete plugin
( function( $ ) {
	var methods = {
		'init' : function( options ) {
			var autocompleteOptions = $.extend({
				'onGetResult' : false
			}, options );
			return this.each(function() {
				var $this = $( this ),
					container = $this.attr( 'id' );
				if ( ! container || $this.attr( 'type' ) != 'text' || $this.size() == 0 ) {
					return;
				}
				var autocomplete = new google.maps.places.Autocomplete( document.getElementById( container ) );
				google.maps.event.addListener( autocomplete, 'place_changed', function() {
					var place = autocomplete.getPlace();
					methods.onGetResult.call( $this, place.geometry.location.lat(), place.geometry.location.lng() );
				});
				$this.data( 'data', {
					'options' : autocompleteOptions
				});
				methods.disabled.call( $this, false );
			});
		}, // end init
		'disabled' : function( value ) {
			if ( value ) {
				$( '.pac-container' ).css({ 'visibility' : 'hidden'	});
			} else {
				$( '.pac-container' ).css({ 'visibility' : 'visible' });
			}
		}, // end disabled
		'onGetResult' : function( lat, lng ) {
			var $this = this,
				options = $this.data( 'data' )['options'];
			if ( typeof options.onGetResult == 'function' ) options.onGetResult.call( $this, lat, lng );
		}
	}
	jQuery.fn.autocomplete = function( method ) {
		if ( methods[ method ] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' + method + ' not found!' );
		}
	}
})( jQuery );

// BWS Google Maps plugin
( function( $ ) {
	var methods = {
		'init' : function( options ) {
			if ( typeof google == 'undefined' ) {
				return;
			}
			var mapOptions = $.extend({
				'mapType'                   : 'roadmap',
				'mapTypeId'                 : google.maps.MapTypeId[options.mapType.toUpperCase()],
				'tilt45'                    : true,
				'autoZoom'                  : true,
				'center'                    : new google.maps.LatLng( 39.639538,-103.007813 ),
				'zoom'                      : 3,
				'draggableCursor'           : 'default',
				'mapTypeControl'            : true,
				'panControl'                : true,
				'rotateControl'             : true,
				'zoomControl'               : true,
				'scaleControl'              : true,
				'streetViewControl'         : true,
				'overviewMapControl'        : false,
				'overviewMapControlOptions' : { 'opened' : false },
				'draggable'                 : true,
				'disableDoubleClickZoom'    : false,
				'scrollwheel'               : true,
				'onZoomChange'              : false,
				'onMapTypeChange'           : false,
				'onTiltChange'              : false,
				'onGetCoordinates'          : false
			}, options );
			this.each(function() {
				var $this = $( this ),
					container = $( this ).attr( 'id' );
				if ( ! container ) {
					return;
				}
				var map = new google.maps.Map( document.getElementById( container ), mapOptions );
				if ( mapOptions.tilt45 ) {
					map.setTilt( 45 );
				} else {
					map.setTilt( 0 );
				}
				$( this ).data( 'data', {
					'container' : container,
					'map'       : map,
					'options'   : mapOptions,
					'markers'   : []
				});
				google.maps.event.addListener( map, 'maptypeid_changed', function() {
					if ( typeof mapOptions.onMapTypeChange === 'function' ) mapOptions.onMapTypeChange.call( $this, map.getMapTypeId() );
				});
				google.maps.event.addListener( map, 'zoom_changed', function() {
					mapOptions.zoom = map.getZoom();
					if ( typeof mapOptions.onZoomChange === 'function' ) mapOptions.onZoomChange.call( $this, map.getZoom() );
				});
				google.maps.event.addListener( map, 'rightclick', function( event ) {
					methods.getContextMenu.call( $this, 'hide', false );
					methods.getContextMenu.call( $this, 'show', {
						'items' : {
							'getCoordinates' : function () {
								if ( typeof mapOptions.onGetCoordinates === 'function' ) mapOptions.onGetCoordinates.call( $this, event.latLng.lat(), event.latLng.lng() );
							}
						}
					});
				});
				$this.on( 'click mouseleave', function() {
					methods.getContextMenu.call( $this, 'hide', false );
				});
			});
		}, // end init
		'getContextMenu' : function( action, data ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'],
					options = $this.data( 'data' )['options'],
					container = $this.data( 'data' )['container'],
					menuPosition = new function() {
						var pageX,
							pageY,
							mouseCoordinates = event || window.event;
						if (mouseCoordinates.pageX || mouseCoordinates.pageY) {
							pageX = mouseCoordinates.pageX;
							pageY = mouseCoordinates.pageY;
						} else if (mouseCoordinates.clientX || mouseCoordinates.clientY) {
							pageX = mouseCoordinates.clientX + document.body.scrollLeft	+ document.documentElement.scrollLeft;
							pageY = mouseCoordinates.clientY + document.body.scrollTop + document.documentElement.scrollTop;
						}
						return {
							'x' : pageX - $this.offset().left,
							'y' : pageY - $this.offset().top
						}
					},
					$menu = $( '<ul/>', {
						'id' : 'gglmps_editor_context_menu'
					}).css({
						'top'  : menuPosition['y'] + 1,
						'left' : menuPosition['x'] + 1
					});
				switch ( action ) {
					case 'show':
						for ( var item in data.items ) {
							var $menuItem = $( '<li/>', {
								'class' : 'gglmps_editor_context_menu_item',
								'text'  : gglmps_translation[ item ]
							}).on( 'click', function() {
								data.items[ item ].call( $this );
								$menu.remove();
							});
							$menu.append( $menuItem );
						}
						$this.append( $menu );
					break;
					case 'hide':
						$this.find( '#gglmps_editor_context_menu' ).remove();
					break; 
				}
			});
		}, // getContextMenu
		'appendMarker' : function( data ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'],
					options = $this.data( 'data' )['options'],
					container = $( '#' + $this.data( 'data' )['container'] ),
					markers = $this.data( 'data' )['markers'],
					bounds = new google.maps.LatLngBounds(),
					lat = data.latlng.split( ',' )[0],
					lng = data.latlng.split( ',' )[1],
					marker = new google.maps.Marker({
						position : new google.maps.LatLng( lat, lng ),
						map      : map
					}),
					infowindow = new google.maps.InfoWindow({
						content  : data.tooltip,
						maxWidth : container.width() - 20
					});
				markers.push({
					'marker'     : marker,
					'infowindow' : infowindow 
				});
				google.maps.event.addListener( marker, 'click', ( function( marker, infowindow ) {
					return function() {
						if ( infowindow.getContent() != '' ) {
							center = map.getCenter();
							infowindow.open( map, marker );
						}
					}
				})( marker, infowindow ));
				google.maps.event.addListener( infowindow, 'closeclick', function() {
					map.panTo( center );
				});
				$.each( markers, function( index, markers ) {
					bounds.extend( markers['marker'].position );
				});
				if ( ! options.autoZoom ) {
					var preZoom = options.zoom;
					map.fitBounds( bounds );
					var boundsListener = google.maps.event.addListener( map, 'bounds_changed', function() {
						map.setZoom( preZoom );
						google.maps.event.removeListener( boundsListener );
					});
				} else {
					map.fitBounds( bounds );
				}
			});
		}, //end appendMarker
		'addMarker' : function ( data ) {
			this.each(function() {
				var $this = $( this );
				if ( data instanceof Array ) {
					for ( var i in data ) {
						methods.appendMarker.call( $this, data[ i ] );
					}
				} else if ( data instanceof Object ) {
					methods.appendMarker.call( $this, data );
				}
			});
		}, // end addMarker
		'deleteMarker' : function( index ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'],
					options = $this.data( 'data' )['options'],
					markers = $this.data( 'data' )['markers'],
					bounds = new google.maps.LatLngBounds();
				if ( index instanceof Array ) {
					for ( var i in index ) {
						markers[ index[ i ] ]['marker'].setMap( null );
						markers.splice( index[ i ], 1 );
					}
				} else {
					markers[ index ]['marker'].setMap( null );
					markers.splice( index, 1 );
				}
				if ( markers.length > 0 ) {
					$.each( markers, function( index, markers ) {
						bounds.extend( markers['marker'].position );
					});
					if ( ! options.autoZoom ) {
						var preZoom = options.zoom;
						map.fitBounds( bounds );
						var boundsListener = google.maps.event.addListener( map, 'bounds_changed', function() {
							map.setZoom( preZoom );
							google.maps.event.removeListener( boundsListener );
						});
					} else {
						map.fitBounds( bounds );
					}
				}
			});
		}, // end deleteMarker
		'updateMarker' : function( data ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'],
					options = $this.data( 'data' )['options'],
					markers = $this.data( 'data' )['markers'];
				markers[ data.index ]['infowindow'].setContent( data.tooltip );
			});
		}, // end updateMarker
		'findMarker' : function( index ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'],
					options = $this.data( 'data' )['options'],
					markers = $this.data( 'data' )['markers'];
				markers[ index ]['marker'].setAnimation( google.maps.Animation.BOUNCE );
				setTimeout( function() {
					markers[ index ]['marker'].setAnimation( null );
				}, 2100 );
			});
		}, // end findMarker
		'resize' : function() {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'],
					options = $this.data( 'data' )['options'],
					markers = $this.data( 'data' )['markers'],
					bounds = new google.maps.LatLngBounds();
					center = map.getCenter();
				google.maps.event.trigger( map , 'resize' );
				$.each( markers, function( index, markers ) {
					bounds.extend( markers['marker'].position );
				});
				if ( ! options.autoZoom ) {
					map.panTo( center );
				} else {
					if ( markers.length > 0 ) {
						map.fitBounds( bounds );
					} else {
						map.panTo( center );
					}
				}
			});	
		}, // end resize
		'setAutoZoom' : function( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'],
					options = $this.data( 'data' )['options'],
					markers = $this.data( 'data' )['markers'],
					bounds = new google.maps.LatLngBounds();
				options.autoZoom = value;
				$.each( markers, function( index, markers ) {
					bounds.extend( markers['marker'].position );
				});
				if ( value && markers.length > 0 ) {
					map.fitBounds( bounds );
				}
			});
		}, // end setAutoZoom
		'setZoom' : function( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'],
					options = $this.data( 'data' )['options'];
				options.zoom = value;
				map.setZoom( value );
			});
		}, // end setZoom
		'getMaxZoom' : function ( type ) {
			var mapTypes = {
			'roadmap'   : 21,
			'satellite' : 19,
			'hybrid'    : 19,
			'terrain'   : 15
			};
			return mapTypes[ type ];
		}, // end getMaxZoom
		'setMapType' : function( type ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				switch ( type ) {
					case 'satellite':
						type = google.maps.MapTypeId.SATELLITE;
						break;
					case 'hybrid':
						type = google.maps.MapTypeId.HYBRID;
						break;
					case 'terrain':
						type = google.maps.MapTypeId.TERRAIN;
						break;
					case 'roadmap':
					default:
						type = google.maps.MapTypeId.ROADMAP;
						break;
				}
				map.setMapTypeId( type );
			});
		}, // end setMapType
		'setTilt' : function ( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				if ( value ) {
					map.setTilt( 45 );
				} else {
					map.setTilt( 0 );
				}
			});
		}, // end setTilt
		'setMapTypeControl' : function ( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				if ( value ) {
					map.setOptions({ 'mapTypeControl' : true });
				} else {
					map.setOptions({ 'mapTypeControl' : false });
				}
			});
		}, // end setMapTypeControl
		'setPanControl' : function ( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				if ( value ) {
					map.setOptions({ 'panControl' : true });
				} else {
					map.setOptions({ 'panControl' : false });
				}
			});
		}, // end setPanControl
		'setRotateControl' : function ( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				if ( value ) {
					map.setOptions({ 'rotateControl' : true });
				} else {
					map.setOptions({ 'rotateControl' : false });
				}
			});
		}, // end setRotateControl
		'setZoomControl' : function ( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				if ( value ) {
					map.setOptions({ 'zoomControl' : true });
				} else {
					map.setOptions({ 'zoomControl' : false });
				}
			});
		}, // end setZoomControl
		'setScaleControl' : function ( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				if ( value ) {
					map.setOptions({ 'scaleControl' : true });
				} else {
					map.setOptions({ 'scaleControl' : false });
				}
			});
		}, // end setScaleControl
		'setStreetViewControl' : function ( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				if ( value ) {
					map.setOptions({ 'streetViewControl' : true });
				} else {
					map.setOptions({ 'streetViewControl' : false });
				}
			});
		}, // end setStreetViewControl
		'setOverviewMapControl' : function ( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				if ( value ) {
					map.setOptions({ 'overviewMapControl' : true });
				} else {
					map.setOptions({ 'overviewMapControl' : false });
				}
			});
		}, // end setOverviewMapControl
		'setOverviewMapControlOpened' : function ( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				if ( value ) {
					map.setOptions({ overviewMapControlOptions : { opened : true } });
				} else {
					map.setOptions({ overviewMapControlOptions : { opened : false } });
				}
			});
		}, // end setOverviewMapControlOpened
		'setDraggable' : function ( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				if ( value ) {
					map.setOptions({ 'draggable' : true });
				} else {
					map.setOptions({ 'draggable' : false });
				}
			});
		}, // end setDraggable
		'setDoubleClick' : function ( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				if ( value ) {
					map.setOptions({ 'disableDoubleClickZoom' : false });
				} else {
					map.setOptions({ 'disableDoubleClickZoom' : true });
				}
			});
		}, // end setDoubleClick
		'setScrollWheel' : function ( value ) {
			this.each(function() {
				var $this = $( this ),
					map = $this.data( 'data' )['map'];
				if ( value ) {
					map.setOptions({ 'scrollwheel' : true });
				} else {
					map.setOptions({ 'scrollwheel' : false });
				}
			});
		} // end setScrollWheel
	} // end methods
	jQuery.fn.bws_googlemaps = function( method ) {
		if ( methods[ method ] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' + method + ' not found!' );
		}
	}
})( jQuery );