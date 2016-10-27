<?php

class CommerceDashboardPanelExtension extends DataExtension
{
    
    /**
     * Setup the default admin panels if none exist
     *
     */
    public function requireDefaultRecords()
    {
        $config = SiteConfig::current_site_config();

        if (!$config->DashboardPanels()->exists()) {
            // Add chart panel
            $panel = DashboardRecentOrdersChartPanel::create();
            $panel->Title = $panel->getLabel();
            $panel->SiteConfigID = $config->ID;
            $panel->SortOrder = 1;
            $panel->write();

            // Add content summary panel
            $panel = DashboardContentSummaryPanel::create();
            $panel->Title = $panel->getLabel();
            $panel->SiteConfigID = $config->ID;
            $panel->SortOrder = 2;
            $panel->write();

            // Add orders list panel
            $panel = DashboardRecentOrdersListPanel::create();
            $panel->Title = $panel->getLabel();
            $panel->SiteConfigID = $config->ID;
            $panel->SortOrder = 3;
            $panel->write();

            // Add low stock items panel
            $panel = DashboardLowStockPanel::create();
            $panel->Title = $panel->getLabel();
            $panel->SiteConfigID = $config->ID;
            $panel->SortOrder = 4;
            $panel->write();

            // Add top products panel
            $panel = DashboardTopProductsPanel::create();
            $panel->Title = $panel->getLabel();
            $panel->SiteConfigID = $config->ID;
            $panel->SortOrder = 5;
            $panel->write();

            // Add new customer panel
            $panel = DashboardNewCustomersPanel::create();
            $panel->Title = $panel->getLabel();
            $panel->SiteConfigID = $config->ID;
            $panel->SortOrder = 6;
            $panel->write();
            
            DB::alteration_message('Created default commerce dashboard', 'created');
        }
    }
}