
/*
 * Created on : Oct 12, 2017, 11:10:47 AM
 * Author: Tran Trong Thang
 * Email: trantrongthang1207@gmail.com
 * Skype: trantrongthang1207
 */
jQuery(document).ready(function ($) {
//    $.ajax({
//        url: $urlbase + 'index.php?option=com_company79&view=wishlist&format=raw',
//        type: 'POST',
//        dataType: 'html',
//        success: function (data, textStatus, jqXHR) {
//            $('#company79').html(data);
//            $(".page-content").LoadingOverlay("hide");
//        },
//        error: function () {
//            $(".page-content").LoadingOverlay("hide");
//        }
//    })
    approvalAdverts('rao-vat', 'index.php?option=com_auto79&view=adverts&format=raw')
    pagination('#rao-vat', '#rao-vat .pagination a', '.page-content');
    pagination('#company79', '#company79 .pagination a', '.page-content');

    function pagination($elContent, $elClick, $elFade) {
        $(document).on('click', $elClick, function (e) {
            e.preventDefault();
            var $me = $(this),
                    $action = $(this).attr('href');
            $($elFade).LoadingOverlay("show");
            $.ajax({
                url: $urlbase + $action + '&format=raw',
                type: 'POST',
                dataType: 'html',
                success: function (data, textStatus, jqXHR) {
                    $($elContent).html(data);
                    $($elFade).LoadingOverlay("hide");
                },
                error: function () {
                    $($elFade).LoadingOverlay("hide");
                }
            })
        })
    }


    function approvalAdverts($elContent, $urlTask) {
        $(".page-content").LoadingOverlay("show");
        $.ajax({
            url: $urlbase + $urlTask,
            type: 'POST',
            dataType: 'html',
            success: function (data, textStatus, jqXHR) {
                $('#' + $elContent).html(data);
                $(".page-content").LoadingOverlay("hide");
            },
            error: function () {
                $(".page-content").LoadingOverlay("hide");
            }
        })
    }
})

