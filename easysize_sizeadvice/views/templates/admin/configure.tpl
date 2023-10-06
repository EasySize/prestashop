<script>
  var es_category_ids = {Product::getProductCategories($product->id)|@json_encode nofilter}
  var es_category_map = {Category::getCategoryInformations(Product::getProductCategories($product->id))|@json_encode nofilter};
  var es_categories = Object.keys(es_category_map).map(function(cat) {
    return es_category_map[cat].name;
  });

  var es_product_stock = {
    {foreach from=$groups key=id_group item=group}
      {if $easysize.EASYSIZE_SIZEADVICE_SIZE_ATTRIBUTE == $id_group}
        {foreach from=$group.attributes key=id_group_attribute item=group_attribute}
          "{$group_attribute.name}": {$group.attributes_quantity[$id_group_attribute]},
        {/foreach}
      {/if}
    {/foreach}
  };

  var es_male_category = "{$easysize.EASYSIZE_SIZEADVICE_MALE_CAT}";
  var es_female_category = "{$easysize.EASYSIZE_SIZEADVICE_FEMALE_CAT}";
  var es_product_gender = 'unisex'

  if (es_category_ids.indexOf(es_male_category) !== -1) {
    es_product_gender = 'male';
  }

  if (es_category_ids.indexOf(es_female_category) !== -1) {
    if (es_product_gender === 'male') {
      es_product_gender = 'unisex';
    } else {
      es_product_gender = 'female';
    }
  }

  var es_size_selector = "{$easysize.EASYSIZE_SIZEADVICE_SIZE_SELECTOR}".replace('(size_attribute)', "{$easysize.EASYSIZE_SIZEADVICE_SIZE_ATTRIBUTE}");

  var es_conf = {
    shop_id: "{$easysize.EASYSIZE_SIZEADVICE_SHOP_ID}",
    placeholder: "{$easysize.EASYSIZE_SIZEADVICE_PLACEHOLDER}".replace('(size_selector)', es_size_selector),
    size_selector: es_size_selector,
    order_button_id: "{$easysize.EASYSIZE_SIZEADVICE_CART_BTN}",
    product_type: es_categories.join(','),
    product_brand: {$product_manufacturer->name|@json_encode nofilter},
    product_gender: es_product_gender,
    product_id: "{$product.id}",
    user_id: "{$easysize.EASYSIZE_SIZEADVICE_SIZE_ATTRIBUTE}",
    image_url: "{$product.cover.bySize.home_default.url}",
    sizes_in_stock: es_product_stock,
    loaded: function(props) {
      var form = document.querySelector('#add-to-cart-or-refresh')
  		var input = document.createElement('input')
  		input.type = "hidden"
  		input.id = "esid-input"
  		input.name = "properties[_esid]"
  		input.value = EasySizeParametersDebug.easysize.pageview_id
  		form.append(input)
    },
  };

  var es_conf_overrides = {$easysize.EASYSIZE_SIZEADVICE_CONF_OVERRIDE|@json_encode nofilter};
  es_conf_overrides = es_conf_overrides.split('\r\n').map((function(str) {
    var data = str.split('==');
    if (data[0]) {
      es_conf[data[0]] = data[1];
    }
  }));

  {$easysize.EASYSIZE_SIZEADVICE_CUSTOM_JS nofilter}


  var es_load_counter = 0;
  var easysize;
  function load_easysize() {
    if (window.EasysizeStarted) {
      return;
    }

    es_load_counter += 1;
    if (!window.EasySize) {
      if (es_load_counter < 10) {
        setTimeout(load_easysize, 50);
      }
    } else {
      easysize = new EasySize(es_conf);
      easysize.start();
    }
  }

  load_easysize();
</script>
