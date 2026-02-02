<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TCM_Admin_Menu
 *
 * Handles the admin menu and reports page rendering.
 */
if (!class_exists('TCM_Admin_Menu')) {
    class TCM_Admin_Menu
    {

        public function __construct()
        {
            add_menu_page(
                'TimeClock Reports',
                'TimeClock',
                'tcm_access',
                'tcm-reports',
                [$this, 'render_reports_page'],
                'dashicons-clock',
                25
            );
        }

        public function render_reports_page()
        {
            TCM_Admin_Reports::render();
        }
    }
    new TCM_Admin_Menu();
}

