<?php
/*
Plugin Name: DB Table Viewer
Description: View, search and paginate database table data in a user-friendly format.
Version: 1.1
Author: Vrutti22
Author URI: https://profiles.wordpress.org/vrutti22/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: db-table-viewer
Domain Path: /languages
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class DBTableViewer {

    public function __construct() {
        // Hooks for admin menu, scripts, and AJAX handlers.
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_get_table_data', [$this, 'get_table_data']);
        add_action('wp_ajax_search_table_data', [$this, 'search_table_data']);
    }

    /**
     * Add the plugin page to the Tools menu.
     */
    public function add_admin_menu() {
        add_management_page(
            __('DB Table Viewer', 'db-table-viewer'),
            __('DB Table Viewer', 'db-table-viewer'),
            'manage_options',
            'db-table-viewer',
            [$this, 'render_admin_page']
        );
    }

    /**
     * Enqueue required scripts and styles for the plugin.
     *
     * @param string $hook The current admin page hook.
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'tools_page_db-table-viewer') {
            return;
        }

        wp_enqueue_script(
            'db-table-viewer-js',
            plugin_dir_url(__FILE__) . 'assets/js/db-table-viewer.js',
            ['jquery'],
            '1.1',
            true
        );
        
        wp_localize_script('db-table-viewer-js', 'DBTableViewer', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'searchingText' => __('Searching...', 'db-table-viewer'),
            'noResultsText' => __('No results found', 'db-table-viewer'),
            'errorText' => __('Error loading data', 'db-table-viewer')
        ]);

        wp_enqueue_style(
            'db-table-viewer-css',
            plugin_dir_url(__FILE__) . 'assets/css/db-table-viewer.css',
            [],
            '1.1'
        );
    }

    /**
     * Render the admin page for the plugin.
     */
    public function render_admin_page() {
        global $wpdb;

        $tables = $wpdb->get_col('SHOW TABLES');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('DB Table Viewer', 'db-table-viewer'); ?></h1>
            <div class="db-viewer-controls">
                <div class="control-group">
                    <label for="db-tables"><?php esc_html_e('Select a Table:', 'db-table-viewer'); ?></label>
                    <select id="db-tables">
                        <option value=""><?php esc_html_e('-- Select a Table --', 'db-table-viewer'); ?></option>
                        <?php foreach ($tables as $table): ?>
                            <option value="<?php echo esc_attr($table); ?>"><?php echo esc_html($table); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="control-group search-group" style="display: none;">
                    <label for="table-search"><?php esc_html_e('Search:', 'db-table-viewer'); ?></label>
                    <input type="text" id="table-search" placeholder="<?php esc_attr_e('Enter search term...', 'db-table-viewer'); ?>">
                    <button type="button" id="search-button" class="button"><?php esc_html_e('Search', 'db-table-viewer'); ?></button>
                    <button type="button" id="clear-search" class="button"><?php esc_html_e('Clear', 'db-table-viewer'); ?></button>
                </div>
            </div>
            <div id="table-data" style="margin-top: 20px;"></div>
        </div>
        <?php
    }

    /**
     * Handle AJAX requests for fetching table data.
     */
    public function get_table_data() {
        global $wpdb;

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized access', 'db-table-viewer'));
        }

        $table_name = sanitize_text_field($_POST['table_name']);
        $page = absint($_POST['page']);
        $rows_per_page = 10;

        if (empty($table_name)) {
            wp_send_json_error(__('Table name is required', 'db-table-viewer'));
        }

        $offset = ($page - 1) * $rows_per_page;
        $data = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `" . esc_sql($table_name) . "` LIMIT %d OFFSET %d", $rows_per_page, $offset),
            ARRAY_A
        );
        $total_rows = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `" . esc_sql($table_name) . "`"));

        $this->send_table_response($data, $total_rows, $page, $rows_per_page);
    }

    /**
     * Handle AJAX requests for searching table data.
     */
    public function search_table_data() {
        global $wpdb;

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized access', 'db-table-viewer'));
        }

        $table_name = sanitize_text_field($_POST['table_name']);
        $search_term = sanitize_text_field($_POST['search_term']);
        $page = absint($_POST['page']);
        $rows_per_page = 10;

        if (empty($table_name)) {
            wp_send_json_error(__('Table name is required', 'db-table-viewer'));
        }

        if (empty($search_term)) {
            wp_send_json_error(__('Search term is required', 'db-table-viewer'));
        }

        $offset = ($page - 1) * $rows_per_page;
        
        // Get column names for the table
        $columns = $wpdb->get_col("SHOW COLUMNS FROM `" . esc_sql($table_name) . "`");
        
        // Build WHERE clause for search across all columns
        $where_conditions = [];
        $placeholders = [];
        
        foreach ($columns as $column) {
            $where_conditions[] = "`" . esc_sql($column) . "` LIKE %s";
            $placeholders[] = '%' . $wpdb->esc_like($search_term) . '%';
        }
        
        $where_clause = implode(' OR ', $where_conditions);
        
        // Get search results
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `" . esc_sql($table_name) . "` WHERE " . $where_clause . " LIMIT %d OFFSET %d",
                array_merge($placeholders, [$rows_per_page, $offset])
            ),
            ARRAY_A
        );
        
        // Get total count for search results
        $total_rows = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM `" . esc_sql($table_name) . "` WHERE " . $where_clause,
                $placeholders
            )
        );

        $this->send_table_response($data, $total_rows, $page, $rows_per_page, $search_term);
    }

    /**
     * Send formatted table response.
     */
    private function send_table_response($data, $total_rows, $page, $rows_per_page, $search_term = '') {
        if (empty($data)) {
            $message = $search_term ? 
                __('No results found for your search', 'db-table-viewer') : 
                __('No data found or table is empty', 'db-table-viewer');
            wp_send_json_error($message);
        }

        $output = '<table class="widefat striped"><thead><tr>';
        foreach (array_keys($data[0]) as $column) {
            $output .= '<th>' . esc_html($column) . '</th>';
        }
        $output .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $output .= '<tr>';
            foreach ($row as $value) {
                // Highlight search term in results if searching
                if ($search_term && stripos($value, $search_term) !== false) {
                    $highlighted_value = preg_replace(
                        '/(' . preg_quote($search_term, '/') . ')/i',
                        '<mark>$1</mark>',
                        esc_html($value)
                    );
                    $output .= '<td>' . $highlighted_value . '</td>';
                } else {
                    $output .= '<td>' . esc_html($value) . '</td>';
                }
            }
            $output .= '</tr>';
        }
        $output .= '</tbody></table>';

        // Pagination
        $total_pages = ceil($total_rows / $rows_per_page);
        $pagination = '<div class="pagination">';
        
        // Previous button
        if ($page > 1) {
            $pagination .= sprintf(
                '<button class="page-button" data-page="%d">&laquo; %s</button>',
                $page - 1,
                __('Previous', 'db-table-viewer')
            );
        }
        
        // Page numbers
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active_class = $i === $page ? ' active' : '';
            $pagination .= sprintf(
                '<button class="page-button%s" data-page="%d">%d</button>',
                $active_class,
                $i,
                $i
            );
        }
        
        // Next button
        if ($page < $total_pages) {
            $pagination .= sprintf(
                '<button class="page-button" data-page="%d">%s &raquo;</button>',
                $page + 1,
                __('Next', 'db-table-viewer')
            );
        }
        
        $pagination .= '</div>';

        // Results info
        $results_info = '<div class="results-info">';
        $start_result = (($page - 1) * $rows_per_page) + 1;
        $end_result = min($page * $rows_per_page, $total_rows);
        
        if ($search_term) {
            $results_info .= sprintf(
                __('Showing %d-%d of %d results for "%s"', 'db-table-viewer'),
                $start_result,
                $end_result,
                $total_rows,
                esc_html($search_term)
            );
        } else {
            $results_info .= sprintf(
                __('Showing %d-%d of %d results', 'db-table-viewer'),
                $start_result,
                $end_result,
                $total_rows
            );
        }
        $results_info .= '</div>';

        wp_send_json_success($results_info . $output . $pagination);
    }
}

new DBTableViewer();