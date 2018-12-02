/**
 *  Nette jQuery skeleton extensions
 *  (c) 2014 ATLAS consulting spol. s r.o.
 *
 *  @Author Radovan Kep�k ( kepak@atlascon.cz )
 *  @depends nette.ajax.js
 *------------------------------------------------------------------------------------------*/

if (!$.nette)
	throw 'nette.skeleton.js requires nette.ajax.js for proper work';

$.nette.ext('custom.input', {
	init: function(){
		this.ext('snippets').after($.proxy(function($el){
			this.setup($el);
		}, this));

		this.setup(document.body);
	}
},{
	/** Find text classes, setup animation for label */
	setup: function( container ){
		$(container).find('div.material > input.material:not([type=file]), div.material > textarea.material').each(function(){
			//Setup input
			$(this).on('focus', function(){
				$(this).parent().addClass('is-active');
			}).on('blur', function(){
				$(this).parent().removeClass('is-active');
			}).on('change', function(){
				$(this).parent('div.material')[this.value ? 'addClass' : 'removeClass']('is-filled');
			});

			//Setup parent
			$(this).trigger('change');
			$(this).parent().addClass('js');

			//If item is already focused
			if($(this).is(':focus'))
				$(this).parent().addClass('is-active');
		});
	}
});
