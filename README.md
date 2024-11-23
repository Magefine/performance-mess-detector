# Magento 2 Performance Monitoring Module

## Overview

This Magento 2 module provides tools to monitor and improve performance by detecting and logging potential bottlenecks in the system. It specifically focuses on tracking slow controller actions, repeated SQL queries, and other performance issues that may impact your Magento store.

---

## Features

- **Controller Performance Tracking**  
  Logs the execution time of all controller actions and identifies actions that exceed a configurable threshold (e.g., 500 ms).

- **SQL Query Optimization**  
  Detects identical or repeated SQL queries executed during a single request cycle.

- **Customizable Thresholds**  
  Allows you to configure thresholds for what constitutes a "slow" action or query via the Magento Admin panel.

- **Detailed Logging**  
  Logs all detected issues, including the following:
    - Full action name of the controller.
    - Class name of the controller.
    - Execution time in milliseconds.

---

## Installation

### 1. Composer Installation
```bash
composer require vendor/performance-mess-detector
bin/magento module:enable Magefine_PerformanceMessDetector
bin/magento setup:upgrade
bin/magento cache:flush
```

### 2. Manual Installation

- **Download or clone this repository into the app/code/Magefine/PerformanceMessDetector directory.**  

- **Run the following commands:**
```bash
bin/magento module:enable Magefine_PerformanceMessDetector
bin/magento setup:upgrade
bin/magento cache:flush
```

### 3. Usage

The module logs detected performance issues in the following log files:

var/log/debug.log

Examples:
```
[2024-11-23T21:46:31.127886+00:00] main.DEBUG: [PMD] Slow action detected : cms_index_index (Magento\Cms\Controller\Index\Index\Interceptor) http://magento.local:8080/ | Execution time : 1027 ms [] []
[PMD] MULTIPLE IDENTICAL SQL QUERIES DETECTED : {"url":"http:\/\/magento.local:8080\/","query":"SELECT `e`.*, IF(at_include_in_menu.value_i...
```
