
<script>
    const languages = [
        @foreach ($languages as $lang)
            {
                label: "{{ $lang['name'] }}",
                value: "{{ $lang['name'] }}"
            },
        @endforeach
    ];
    VirtualSelect.init({
        ele: '#languages',
        multiple: true,
        options: languages,
        disableSelectAll:true,
        maxValues:5,
        placeholder:'{{__("Select Languages")}}',
        noOptionsText:'{{__("No options found")}}',
        noSearchResultsText:'{{__("No options found")}}',
        searchPlaceholderText:'{{__("Search")}}...',
        allOptionsSelectedText:'{{__("All")}}',
        optionsSelectedText: '{{__("options selected c")}}',
        additionalClasses:'partner-field',
        selectedValue: <?=json_encode($vendor_languages)?>
    });
    $('#languages').on('change', function (e) {
        @this.set('vendor_languages', $(this).val());
    });
    const countries = [
        @foreach ($countries as $item)
            {
                label: "{{ $item['name']['common'] }}",
                value: "{{ $item['name']['common'] }}"
            },
        @endforeach
    ];
    VirtualSelect.init({
        ele: '#country',
        options: countries,
        placeholder:'{{__("Select Country")}}',
        additionalClasses:'partner-field',
        noOptionsText:'{{__("No options found")}}',
        noSearchResultsText:'{{__("No options found")}}',
        searchPlaceholderText:'{{__("Search")}}...',
        allOptionsSelectedText:'{{__("All")}}',
        optionsSelectedText: '{{__("options selected c")}}',
        search:true,
        selectedValue: '<?=$country?>'
    });
    $('#country').on('change', function (e) {
        @this.set('country', $(this).val());
    });
    VirtualSelect.init({
        ele: '#nationality',
        options: countries,
        placeholder:'{{__("Select Nationality")}}',
        additionalClasses:'partner-field',
        noOptionsText:'{{__("No options found")}}',
        noSearchResultsText:'{{__("No options found")}}',
        searchPlaceholderText:'{{__("Search")}}...',
        allOptionsSelectedText:'{{__("All")}}',
        optionsSelectedText: '{{__("options selected c")}}',
        search:true,
        selectedValue: '<?=$nationality?>'
    });
    $('#nationality').on('change', function (e) {
        @this.set('nationality', $(this).val());
    });

    const phoneCodes = [
        @foreach ($countries as $codes)
            @foreach ($codes['idd']['suffixes'] as $suffix)
                {
                    label: "{{ $codes['idd']['root'].$suffix }}",
                    value: "{{ $codes['idd']['root'].$suffix  }}"
                },
            @endforeach
        @endforeach
    ];
    VirtualSelect.init({
        ele: '#phoneCode',
        options: phoneCodes,
        placeholder:'{{__("Phone Code")}}',
        additionalClasses:'phone-code-field',
        noOptionsText:'{{__("No options found")}}',
        noSearchResultsText:'{{__("No options found")}}',
        searchPlaceholderText:'{{__("Search")}}...',
        allOptionsSelectedText:'{{__("All")}}',
        optionsSelectedText: '{{__("options selected c")}}',
        search:true,
    });
    $('#phoneCode').on('change', function (e) {
        @this.set('phoneCode', $(this).val());
    });

    const arbitrators = [
        @foreach ($arbitrators as $item)
            {
                label: "{{ $item['name'] }}",
                value: "{{ $item['id']  }}"
            },
        @endforeach
    ];
    VirtualSelect.init({
        ele: '#arbitrators',
        options: arbitrators,
        placeholder:'{{__("Select Legal Consultant")}}',
        additionalClasses:'partner-field',
        noOptionsText:'{{__("No options found")}}',
        noSearchResultsText:'{{__("No options found")}}',
        searchPlaceholderText:'{{__("Search")}}...',
        allOptionsSelectedText:'{{__("All")}}',
        optionsSelectedText: '{{__("options selected")}}',
        search:true,
        multiple:true,
        selectedValue: <?=json_encode($vendor_arbitrators)?>
    });
    $('#arbitrators').on('change', function (e) {
        @this.set('vendor_arbitrators', $(this).val());
    });

    const jurisdictions = [
        @foreach ($jurisdictions as $item)
            {
                label: "{{ $item['name'] }}",
                value: "{{ $item['id']  }}"
            },
        @endforeach
    ];
    VirtualSelect.init({
        ele: '#jurisdictions',
        options: jurisdictions,
        placeholder:'{{__("Select Jurisdictions")}}',
        additionalClasses:'partner-field',
        noOptionsText:'{{__("No options found")}}',
        noSearchResultsText:'{{__("No options found")}}',
        searchPlaceholderText:'{{__("Search")}}...',
        allOptionsSelectedText:'{{__("All")}}',
        optionsSelectedText: '{{__("options selected c")}}',
        search:true,
        multiple:true,
        selectedValue: <?=json_encode($vendor_jurisdictions)?>
    });
    $('#jurisdictions').on('change', function (e) {
        @this.set('vendor_jurisdictions', $(this).val());
    });

    const categories = [
        @foreach ($categories as $item)
            {
                label: "{{ $item['name'] }}",
                value: "{{ $item['id']  }}"
            },
        @endforeach
    ];
    VirtualSelect.init({
        ele: '#categories',
        options: categories,
        placeholder:'{{__("Select Categories")}}',
        additionalClasses:'partner-field',
        noOptionsText:'{{__("No options found")}}',
        noSearchResultsText:'{{__("No options found")}}',
        searchPlaceholderText:'{{__("Search")}}...',
        allOptionsSelectedText:'{{__("All")}}',
        optionsSelectedText: '{{__("options selected c")}}',
        search:true,
        multiple:true,
        allowNewOption: true,
        selectedValue: <?=json_encode($vendor_categories)?>
    });
    $('#categories').on('change', function (e) {
        @this.set('vendor_categories', $(this).val());
        @this.call('getSubCategories');
    });
    $('#categories').on('reset', function (e) {
        @this.call('getSubCategories');
        Livewire.dispatch('updateSubCategories', []);
    });

    const sub_categories = [
        @foreach ($sub_categories as $item)
            {
                label: "{{ $item['name'] }}",
                value: "{{ $item['id']  }}"
            },
        @endforeach
    ];
    VirtualSelect.init({
        ele: '#sub_categories',
        options: sub_categories,
        placeholder:'{{__("Select Sub Categories")}}',
        additionalClasses:'partner-field',
        noOptionsText:'{{__("No options found")}}',
        noSearchResultsText:'{{__("No options found")}}',
        searchPlaceholderText:'{{__("Search")}}...',
        allOptionsSelectedText:'{{__("All")}}',
        optionsSelectedText: '{{__("options selected c")}}',
        search:true,
        multiple:true,
        selectedValue: <?=json_encode($vendor_sub_categories)?>
    });
    $('#sub_categories').on('change', function (e) {
        @this.set('vendor_sub_categories', $(this).val());
    });

    Livewire.on('updateSubCategories', (subCategories) => {
        const subCategoriesOptions = subCategories.map((subCategory) => {
            return {
                label: subCategory.name,
                value: subCategory.id
            };
        });
        console.log(subCategoriesOptions);
        document.querySelector('#sub_categories').destroy();
        // Update options of sub-categories dropdown
        VirtualSelect.init({
            ele: '#sub_categories',
            options: subCategoriesOptions,
            placeholder: '{{__("Select Sub Categories")}}',
            additionalClasses: 'partner-field',
            noOptionsText:'{{__("No options found")}}',
            noSearchResultsText:'{{__("No options found")}}',
            searchPlaceholderText:'{{__("Search")}}...',
            allOptionsSelectedText:'{{__("All")}}',
            optionsSelectedText: '{{__("options selected c")}}',
            search: true,
            multiple: true
        });
    });
</script>