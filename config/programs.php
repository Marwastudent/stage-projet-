<?php

return [
    'pdf_template' => env('PROGRAM_PDF_TEMPLATE', storage_path('app/templates/program-template.pdf')),
    'pdf_rows_per_page' => (int) env('PROGRAM_PDF_ROWS_PER_PAGE', 10),
];
