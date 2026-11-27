/* Comparison tables in article bodies (.compare-table).
   1. Copies each column heading into data-label on its cells, so the phone layout (enhance.css) can show it.
   2. Sets ARIA roles: once a table is laid out as blocks on phones, some browsers drop its table semantics.
   Runs on every page; it does nothing when there is no .compare-table. Works for articles already in the database. */
(function () {
    'use strict';

    function enhance(table) {
        if (table.getAttribute('data-enhanced') === '1') { return; }
        var headRow = table.querySelector('thead tr');
        if (!headRow) { return; }

        var headers = Array.prototype.map.call(headRow.children, function (th) {
            return th.textContent.replace(/\s+/g, ' ').trim();
        });

        table.setAttribute('role', 'table');
        table.setAttribute('data-enhanced', '1');
        table.querySelectorAll('thead, tbody').forEach(function (group) {
            group.setAttribute('role', 'rowgroup');
        });
        Array.prototype.forEach.call(headRow.children, function (th) {
            th.setAttribute('role', 'columnheader');
        });

        table.querySelectorAll('tbody tr').forEach(function (tr) {
            tr.setAttribute('role', 'row');
            Array.prototype.forEach.call(tr.children, function (td, i) {
                // The first cell is the row title: the phone card shows it as its heading, without a label.
                if (i > 0 && headers[i] && !td.hasAttribute('data-label')) {
                    td.setAttribute('data-label', headers[i]);
                }
                td.setAttribute('role', i === 0 ? 'rowheader' : 'cell');
            });
        });
    }

    Array.prototype.forEach.call(document.querySelectorAll('.compare-table'), enhance);
})();
