# DB Table Viewer (with search)

**Contributors:** Vrutika 
**Tags:** database, table, viewer, pagination, admin  
**Requires at least:** 5.0  
**Tested up to:** 6.7  
**Requires PHP:** 7.2  
**Stable tag:** 1.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

A WordPress plugin to display database table data with pagination in a user-friendly format.

## Description

DB Table Viewer is a simple plugin that allows administrators to:
- Select any table in the WordPress database.
- View its data in a clean, paginated HTML table.
- Navigate through rows using AJAX-based pagination.
- Added search option

This plugin is particularly useful for debugging or managing custom database tables without using SQL queries directly.

## Features

- Dropdown to select from available database tables.
- Displays table data in a well-formatted HTML table.
- AJAX-based pagination for seamless navigation.
- Secure implementation with proper sanitization and validation.
- Fully translation-ready.

## Installation

1. Download the plugin ZIP file.
2. Upload the folder `db-table-viewer` to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Navigate to **Tools > DB Table Viewer** to use the plugin.

## Usage

1. Go to **Tools > DB Table Viewer** in the WordPress admin dashboard.
2. Select a database table from the dropdown menu.
3. View the table's data with pagination.

## Screenshots
1. **Menu**  
   A dropdown to select the database table.  

   ![Menu](https://prnt.sc/_YbSSzGMKxKP)

1. **Table Selection Dropdown**  
   A dropdown to select the database table.  

   ![Table Selection](https://prnt.sc/Fe-JOpRadlen)

2. **Table Data View**  
   A paginated view of the selected table's data.  

   ![Data View](https://prnt.sc/1H_e2xbP8vSS)

## Frequently Asked Questions

### Who can use this plugin?
Only administrators with `manage_options` capability can access the plugin's features.

### Can this plugin modify database data?
No, the plugin is read-only and does not allow any modifications to the database.

### What happens if I select a table with no data?
The plugin will display a message indicating that the table is empty.

## Changelog

### 1.0
- Initial release.
- Dropdown to select database tables.
- AJAX-based table data display with pagination.

## License

This plugin is licensed under the GPLv2 or later. See the [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html) for details.

---

**Enjoy using DB Table Viewer?** Feel free to provide feedback or contribute to its development!
