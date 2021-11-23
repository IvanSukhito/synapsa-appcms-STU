<script type="text/javascript">
    'use strict';
    $(document).ready(function() {
        $('.dropify').dropify();
        //$( 'textarea.editor' ).ckeditor();
        $('.texteditor').each(function(i, item) {
            CKEDITOR.replace(item.id, {
                autoParagraph: true,
                allowedContent: true,
                extraAllowedContent: '*(*);*{*};*[*]{*};div(class);span(class);h5[*]',
                extraPlugins: 'justify,format,colorbutton,font,smiley'
            });
        });

        $('.datemonthyear').datepicker({
            viewMode: 1,
            minViewMode: 1,
            format: 'yyyy-mm',
            weekStart:0,
            todayHighlight: true,
            autoclose: true
        });

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });

        $('.datetime').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss'
        });

        $('.datetime2').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            inline: true
        });

        $('.time').datetimepicker({
            format: 'HH:mm:ss'
        });

        $('.daterange').daterangepicker({
            // timePicker: true,
            // timePicker24Hour: true,
            // timePickerIncrement: 15,
            locale: {
                "format": "YYYY-MM-DD",
                "separator": " | "
            }
        });

        $('.daterangetime').daterangepicker({
            timePicker: true,
            timePicker24Hour: true,
            // timePickerIncrement: 15,
            locale: {
                "format": "YYYY-MM-DD HH:mm",
                "separator": " | "
            }
        });

        $('.timerange').daterangepicker({
            timePicker: true,
            timePicker24Hour: true,
            autoUpdateInput: false,
            // timePickerIncrement: 15,
            locale: {
                "format": "HH:mm",
                "separator": " - "
            }
        }).on('show.daterangepicker', function (ev, picker) {
            picker.container.find(".calendar-table").hide();
        }).on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('HH:mm') + ' - ' + picker.endDate.format('HH:mm'));
        });

        $('.setcolorpicker').colorpicker();

        $('.select2').select2();
        $('.tagging').select2({
            tags: true
        });
        $('.multiselect2').select2({
            tags: true
        });

        $('.money-format').inputmask('numeric', {
            radixPoint: ".",
            groupSeparator: ",",
            digits: 2,
            autoGroup: true,
            prefix: '', //Space after $, this will not truncate the first character.
            rightAlign: false
        });
    });

    function add_image(curr) {
        let $getInput = $(curr).parent().find('div.upload-image').children();
        let $clone = '';
        $.each($getInput, function(index, item) {
            $clone = $(item).clone().val("");
        });
        $(curr).parent().find('div.upload-image').append($clone);
        return false;
    }

    function remove_image(curr) {
        let ListId = '';
        let storageData = $(curr).data('storage');
        let removeId = $(curr).data('id');
        $.each($(curr).parent().parent().find('div.showing-image'), function(index, item) {
            if (parseInt(removeId) !== parseInt($(item).data('id'))) {
                if (ListId.length <= 0) {
                    ListId += $(item).data('id');
                }
                else {
                    ListId += ',' + $(item).data('id');
                }
            }
        });
        $(curr).parent().remove();
        $("#" + storageData).val(ListId);
        return false;
    }

    function removeFormatMoney(money) {
        if (typeof money !== "undefined") {
            return Number(money.replace(/[^0-9.-]+/g,""));
        }
        else {
            return 0;
        }
    }

    function setFormatMoney(number, decPlaces) {
        if (typeof number !== "undefined") {
            return setFormatMoney(number, decPlaces, '.', ',');
        }
        else {
            return 0;
        }
    }

    function setFormatMoney(number, decPlaces, decSep, thouSep) {
        if (typeof number !== "undefined") {
            decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces;
            decSep = typeof decSep === "undefined" ? "." : decSep;
            thouSep = typeof thouSep === "undefined" ? "," : thouSep;
            let sign = number < 0 ? "-" : "";
            let i = String(parseInt(number = Math.abs(Number(number) || 0).toFixed(decPlaces)));
            let j = i.length;
            j = j > 3 ? j % 3 : 0;

            return sign +
                (j ? i.substr(0, j) + thouSep : "") +
                i.substr(j).replace(/(\decSep{3})(?=\decSep)/g, "$1" + thouSep) +
                (decPlaces ? decSep + Math.abs(number - i).toFixed(decPlaces).slice(2) : "");
        }
        else {
            return 0;
        }
    }

</script>
