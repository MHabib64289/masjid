<?php
header("Content-type: text/css");
?>

/* Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #16A085;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #138a72;
}

/* Form Focus States */
input:focus, select:focus, textarea:focus {
    outline: 2px solid #16A085;
    outline-offset: -1px;
}

/* Table Hover Effects */
tr:hover td {
    background-color: rgba(22, 160, 133, 0.05);
}

/* Link Transitions */
a {
    transition: all 0.2s ease-in-out;
}

/* Toast Messages */
.toast {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 50;
    padding: 1rem;
    border-radius: 0.5rem;
    background: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: transform 0.2s ease-out;
}

.toast.success {
    border-left: 4px solid #16A085;
}

.toast.error {
    border-left: 4px solid #E74C3C;
}

/* Input Placeholders */
::placeholder {
    color: #94A3B8;
    opacity: 1;
}

/* Loading States */
.loading {
    position: relative;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(2px);
}

/* Print Styles */
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .print-break {
        page-break-before: always;
    }
}

body {
    font-family: Arial, sans-serif;
    background: #f0f2f5;
    color: #333;
}
.container {
    width: 90%;
    max-width: 800px;
    margin: 50px auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
}
body { font-family: Arial; background: #f0f0f0; padding: 20px; }
input, button { padding: 10px; margin: 5px; display: block; }

body { font-family: Arial; background: #f0f0f0; padding: 20px; }
input, button, select, textarea { padding: 10px; margin: 5px; display: block; }
table { background: white; border-collapse: collapse; width: 100%; margin-top: 20px; }
td, th { border: 1px solid #ccc; padding: 8px; text-align: left; }
h2 { color: darkblue; }

