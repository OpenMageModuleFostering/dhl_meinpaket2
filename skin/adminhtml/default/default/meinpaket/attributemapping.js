/**
 * JavaScript attribute mapping helpers.
 * 
 * @category skin
 * @package Dhl_MeinPaket
 * @author Timo Fuchs <timo.fuchs@aoemedia.de>
 */

// setup namespace object
if (typeof MeinPaket === 'undefined') {
	var MeinPaket = {};
}

/**
 * Attribute helper class.
 */
MeinPaket.Attribute = Class.create();
MeinPaket.Attribute.prototype = {

	/**
	 * Number of attribute mappings.
	 * 
	 * @var integer
	 */
	numItems : 0,

	/**
	 * Prefix.
	 * 
	 * @var string
	 */
	attributeMappingIndexPrefix : 'meinpaket_attribute_mapping_',

	/**
	 * Constructor.
	 * 
	 * @return void
	 */
	initialize : function() {
	},

	/**
	 * Adds a attribute mapping interface.
	 * 
	 * @return void
	 */
	add : function() {
		var index = this.numItems;
		var tpl = new Template(this.template);
		var data = {
			index : index,
			prefix : this.attributeMappingIndexPrefix,
			magentoAttributesSelect : this.renderMagentoAttributeSelect(
					MeinPaket.magentoAttributes, index),
			meinpaketAttributesSelect : this.renderMeinpaketAttributeSelect(
					MeinPaket.meinpaketAttributes, index),
			labelMagentoAttribute : MeinPaket.locale.magentoAttribute,
			labelMeinPaketAttribute : MeinPaket.locale.meinpaketAttribute,
			labelMagentoValue : MeinPaket.locale.magentoValue,
			labelMeinPaketValue : MeinPaket.locale.meinpaketValue,
			labelEditValueMapping : MeinPaket.locale.editValueMapping,
			labelDeleteValueMapping : MeinPaket.locale.deleteValueMapping
		};

		this.top = $('meinpaket_attribute_top');

		Element.insert(this.top, {
			'after' : tpl.evaluate(data)
		});
		// this.top = $(this.idLabel + '_' + index);
		this.numItems++;
	},

	deleteAttributeMapping : function(index) {
		$(this.attributeMappingIndexPrefix + index).remove();
	},

	/**
	 * Template html for attribute mappings.
	 * 
	 * @var string
	 */
	template : '<div id="#{prefix}#{index}"  class="option-box"> '
			+ '<table class="option-header" cellpadding="0" cellspacing="0">'
			+ '<thead>'
			+ '<tr>'
			+ '<th class="opt-type">#{labelMagentoAttribute}</th>'
			+ '<th class="opt-req">#{labelMeinPaketAttribute}</th>'
			+ '<th>&nbsp;</th>'
			+ '<th>&nbsp;</th>'
			+ '</tr>'
			+ '</thead>'
			+ '<tbody id="attribute_table_#{index}">'
			+ '<tr>'
			+

			'<td>#{magentoAttributesSelect}</td>'
			+ '<td>#{meinpaketAttributesSelect}</td>'
			+ '<td>'
			+ '<button type="button" value="" class="scalable add" onclick="meinPaketAttribute.editValueMapping(#{index});"><span>#{labelEditValueMapping}</span></button>'
			+ '</td>'
			+ '<td>'
			+ '<button type="button" value="" class="scalable delete" onclick="meinPaketAttribute.deleteAttributeMapping(#{index});"><span>#{labelDeleteValueMapping}</span></button>'
			+ '</td>' + '</tr>'
			+ '<tr id="values_row_#{index}"><td colspan="4"></td></tr>'
			+ '</tbody>' + '</table>'
			+ '<div id="values_container_#{index}"></div>' + '</div>',

	/**
	 * Renders an attribute select box.
	 * 
	 * @param Array
	 *            attributes An Array of objects which have "id" and "name"
	 *            properties.
	 * @param string
	 *            type Must be "magento" or "meinpaket".
	 * @param integer
	 *            id Index of the current mapping.
	 * @return String
	 */
	renderAttributeSelect : function(attributes, type, id) {
		var html = '<select id="select_' + type + '_attribute_' + id
				+ '" name="' + type + '_attribute_id[' + id + ']">';
		for (var i = 0; i < attributes.length; i++) {
			html += '<option value="' + attributes[i].id + '">'
					+ attributes[i].name + '</option>';
		}
		html += '</select>';
		return html;
	},

	/**
	 * Renders a Magento attribute select box.
	 * 
	 * @see MeinPaket.Attribute.renderAttributeSelect()
	 * @param Array
	 *            attributes
	 * @param integer
	 *            id
	 * @return String
	 */
	renderMagentoAttributeSelect : function(attributes, id) {
		return this.renderAttributeSelect(attributes, 'magento', id);
	},

	/**
	 * Renders a MeinPaket attribute select box.
	 * 
	 * @see MeinPaket.Attribute.renderAttributeSelect()
	 * @param Array
	 *            attributes
	 * @param integer
	 *            id
	 * @return String
	 */
	renderMeinpaketAttributeSelect : function(attributes, id) {
		return this.renderAttributeSelect(attributes, 'meinpaket', id);
	},

	/**
	 * Creates a mapping table for the attribute mapping with the given id.
	 * 
	 * @param integer
	 *            index
	 * @return void
	 */
	editValueMapping : function(index) {
		var container = $(this.attributeMappingIndexPrefix + index);
		var elements = [ $("select_attribute_set"), $("select_variant"),
				$("select_magento_attribute_" + index),
				$("select_meinpaket_attribute_" + index) ].flatten();
		var serializedElements = Form.serializeElements(elements)
				+ '&attribute_mapping_id=' + index;
		new Ajax.Updater("attribute_table_" + index,
				MeinPaket.url.editAttributeValues, {
					parameters : serializedElements,
					evalScripts : true,
					insertion : 'bottom',
					onComplete : function() {
						// $("select_magento_attribute_"+index).disabled = true;
						// $("select_meinpaket_attribute_"+index).disabled =
						// true;
						// $(\'save_button\').disabled = false;
						// Event.observe($("select_itemtype"), \'change\',
						// itemType.updateAttributes);
					}
				});
	}
};

// create attribute instance
var meinPaketAttribute = new MeinPaket.Attribute();