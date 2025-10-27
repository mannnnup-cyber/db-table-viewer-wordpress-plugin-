jQuery(document).ready(function ($) {
    function loadTableData(tableName, page) {
        const $tableData = $('#table-data');
        $tableData.html('<p>Loading...</p>');

        $.post(DBTableViewer.ajaxUrl, {
            action: 'get_table_data',
            table_name: tableName,
            page: page,
        }).done(function (response) {
            if (response.success) {
                $tableData.html(response.data);
                $('.page-button').click(function () {
                    const newPage = $(this).data('page');
                    loadTableData(tableName, newPage);
                });
            } else {
                $tableData.html('<p>' + response.data + '</p>');
            }
        }).fail(function () {
            $tableData.html('<p>Error loading data</p>');
        });
    }

    $('#db-tables').change(function () {
        const tableName = $(this).val();
        if (tableName) {
            loadTableData(tableName, 1);
        } else {
            $('#table-data').html('');
        }
    });
});
