import { Component } from 'src/core/shopware';
import template from './swag-migration-data-selector.html.twig';
import './swag-migration-data-selector.less';


Component.register('swag-migration-data-selector', {
    template,

    props: {
        targets: {
            type: Array
        },
        tableData: {
            type: Array
        }
    },

    watch: {
        targets: {
            handler: function targetHandler() {
                if (this.targets.length < 1) {
                    return;
                }

                this.tableData.forEach((data) => {
                    data.targetId = this.targets[0].id;
                });
            },
            immediate: true
        }
    },

    mounted() {
        this.$refs.tableDataGrid.selectAll(true);
    },

    methods: {
        getSelectedData() {
            return this.$refs.tableDataGrid.getSelection();
        },

        getTableData() {
            return this.$refs.tableDataGrid.getSelection();
        }
    }
});