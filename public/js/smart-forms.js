/**
 * Smart Forms - Autocomplete & Better UX for all forms
 * Provides intelligent suggestions based on existing data
 */

class SmartForms {
    constructor() {
        this.cache = {};
        this.recentItems = this.loadRecent();
    }

    /**
     * Initialize autocomplete for an input field
     * @param {string} inputId - Input element ID
     * @param {string} dataSource - API endpoint or data array
     * @param {object} options - Configuration options
     */
    initAutocomplete(inputId, dataSource, options = {}) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const config = {
            minChars: 0, // Show on focus
            maxResults: 10,
            placeholder: options.placeholder || 'Type or select...',
            recentLabel: 'ថ្មីៗ (Recent)',
            suggestionsLabel: 'ស្នើរ (Suggestions)',
            ...options
        };

        // Create dropdown
        const dropdown = this.createDropdown(input);
        
        // Show on focus
        input.addEventListener('focus', () => this.showSuggestions(input, dropdown, dataSource, config));
        
        // Filter on input
        input.addEventListener('input', () => this.filterSuggestions(input, dropdown, dataSource, config));
        
        // Hide on blur (with delay for clicks)
        input.addEventListener('blur', () => setTimeout(() => dropdown.classList.remove('show'), 200));
        
        // Keyboard navigation
        this.setupKeyboardNav(input, dropdown);
    }

    createDropdown(input) {
        const existing = input.nextElementSibling;
        if (existing?.classList.contains('smart-dropdown')) {
            return existing;
        }

        const dropdown = document.createElement('div');
        dropdown.className = 'smart-dropdown';
        dropdown.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,.15);
            z-index: 1000;
            margin-top: 4px;
            display: none;
        `;

        // Make input container relative
        input.style.position = 'relative';
        const parent = input.parentElement;
        parent.style.position = 'relative';
        parent.appendChild(dropdown);

        return dropdown;
    }

    async showSuggestions(input, dropdown, dataSource, config) {
        const query = input.value.trim().toLowerCase();
        let data = [];

        // Get data from source
        if (typeof dataSource === 'string') {
            // API endpoint
            data = await this.fetchData(dataSource, query);
        } else if (Array.isArray(dataSource)) {
            // Direct array
            data = dataSource;
        }

        // Add recent items
        const recentKey = input.id || input.name;
        const recent = this.recentItems[recentKey] || [];

        // Build dropdown HTML
        let html = '';

        // Recent items (if no query)
        if (!query && recent.length > 0) {
            html += `<div class="dropdown-section">
                <div class="dropdown-header">${config.recentLabel}</div>
                ${recent.slice(0, 5).map(item => this.renderItem(item, true)).join('')}
            </div>`;
        }

        // Suggestions
        if (data.length > 0) {
            const filtered = query 
                ? data.filter(item => this.matchItem(item, query))
                : data.slice(0, config.maxResults);

            if (filtered.length > 0) {
                html += `<div class="dropdown-section">
                    <div class="dropdown-header">${config.suggestionsLabel}</div>
                    ${filtered.map(item => this.renderItem(item)).join('')}
                </div>`;
            }
        }

        // Empty state
        if (!html) {
            html = '<div class="dropdown-empty">Type to search...</div>';
        }

        dropdown.innerHTML = html;
        dropdown.classList.add('show');
        dropdown.style.display = 'block';

        // Attach click handlers
        dropdown.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', () => {
                input.value = item.dataset.value;
                this.addRecent(recentKey, item.dataset.value);
                dropdown.classList.remove('show');
                dropdown.style.display = 'none';
                input.dispatchEvent(new Event('change'));
            });
        });
    }

    filterSuggestions(input, dropdown, dataSource, config) {
        // Same as showSuggestions but triggered on input
        this.showSuggestions(input, dropdown, dataSource, config);
    }

    renderItem(item, isRecent = false) {
        const value = typeof item === 'string' ? item : item.name || item.value;
        const icon = isRecent ? '<i class="bi bi-clock-history"></i>' : '<i class="bi bi-check2"></i>';
        
        return `<div class="dropdown-item" data-value="${value}" style="
            padding: 8px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            transition: background 0.15s;
        " onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
            <span style="color: #94a3b8; font-size: 0.75rem;">${icon}</span>
            <span>${value}</span>
        </div>`;
    }

    matchItem(item, query) {
        const value = typeof item === 'string' ? item : item.name || item.value || '';
        return value.toLowerCase().includes(query);
    }

    setupKeyboardNav(input, dropdown) {
        input.addEventListener('keydown', (e) => {
            const items = dropdown.querySelectorAll('.dropdown-item');
            if (items.length === 0) return;

            const active = dropdown.querySelector('.dropdown-item.active');
            let index = Array.from(items).indexOf(active);

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                index = (index + 1) % items.length;
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                index = index <= 0 ? items.length - 1 : index - 1;
            } else if (e.key === 'Enter' && active) {
                e.preventDefault();
                active.click();
                return;
            } else if (e.key === 'Escape') {
                dropdown.classList.remove('show');
                dropdown.style.display = 'none';
                return;
            }

            // Update active state
            items.forEach((item, i) => {
                if (i === index) {
                    item.classList.add('active');
                    item.style.background = '#eef2ff';
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('active');
                    item.style.background = 'white';
                }
            });
        });
    }

    async fetchData(endpoint, query) {
        const cacheKey = `${endpoint}:${query}`;
        if (this.cache[cacheKey]) {
            return this.cache[cacheKey];
        }

        try {
            const url = query ? `${endpoint}?q=${encodeURIComponent(query)}` : endpoint;
            const response = await fetch(url);
            const data = await response.json();
            this.cache[cacheKey] = data;
            return data;
        } catch (error) {
            console.error('Autocomplete fetch error:', error);
            return [];
        }
    }

    addRecent(key, value) {
        if (!this.recentItems[key]) {
            this.recentItems[key] = [];
        }

        // Remove if exists, add to front
        this.recentItems[key] = this.recentItems[key].filter(v => v !== value);
        this.recentItems[key].unshift(value);

        // Keep only last 10
        this.recentItems[key] = this.recentItems[key].slice(0, 10);

        // Save to localStorage
        this.saveRecent();
    }

    loadRecent() {
        try {
            const data = localStorage.getItem('smartforms_recent');
            return data ? JSON.parse(data) : {};
        } catch {
            return {};
        }
    }

    saveRecent() {
        try {
            localStorage.setItem('smartforms_recent', JSON.stringify(this.recentItems));
        } catch (error) {
            console.error('Could not save recent items:', error);
        }
    }

    /**
     * Smart item name autocomplete with category awareness
     */
    initItemNameAutocomplete(inputElement, categoryElement) {
        const dropdown = this.createDropdown(inputElement);

        const showItemSuggestions = async () => {
            const category = categoryElement?.value || 'all';
            const query = inputElement.value.trim().toLowerCase();

            // Fetch items based on category
            const endpoint = `/api/procurement/items?category=${category}`;
            const items = await this.fetchData(endpoint, query);

            let html = '';
            if (items.length > 0) {
                html = `<div class="dropdown-section">
                    <div class="dropdown-header">Suggested Items</div>
                    ${items.map(item => `
                        <div class="dropdown-item" data-value="${item.name}" data-unit="${item.unit}" data-price="${item.price || ''}"
                             style="padding: 8px 12px; cursor: pointer; font-size: 0.85rem;"
                             onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                            <div style="font-weight: 600;">${item.name}</div>
                            <div style="font-size: 0.75rem; color: #94a3b8;">
                                ${item.unit ? `Unit: ${item.unit}` : ''}
                                ${item.price ? ` • Last price: $${item.price}` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>`;
            }

            dropdown.innerHTML = html || '<div class="dropdown-empty">No suggestions</div>';
            dropdown.classList.add('show');
            dropdown.style.display = 'block';

            // Auto-fill on select
            dropdown.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', () => {
                    inputElement.value = item.dataset.value;
                    
                    // Auto-fill unit and price if available
                    const row = inputElement.closest('tr');
                    if (row) {
                        const unitSelect = row.querySelector('select[name*="[unit]"]');
                        const priceInput = row.querySelector('input[name*="[unit_price]"]');
                        
                        if (unitSelect && item.dataset.unit) {
                            unitSelect.value = item.dataset.unit;
                        }
                        if (priceInput && item.dataset.price) {
                            priceInput.value = item.dataset.price;
                            // Trigger calculation
                            priceInput.dispatchEvent(new Event('input'));
                        }
                    }

                    dropdown.style.display = 'none';
                });
            });
        };

        inputElement.addEventListener('focus', showItemSuggestions);
        inputElement.addEventListener('input', showItemSuggestions);
        inputElement.addEventListener('blur', () => setTimeout(() => dropdown.style.display = 'none', 200));
        
        // Re-show when category changes
        if (categoryElement) {
            categoryElement.addEventListener('change', showItemSuggestions);
        }
    }
}

// Initialize globally
window.smartForms = new SmartForms();

// CSS for dropdowns
const style = document.createElement('style');
style.textContent = `
    .smart-dropdown.show { display: block !important; }
    .dropdown-header {
        padding: 6px 12px;
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }
    .dropdown-empty {
        padding: 16px;
        text-align: center;
        color: #94a3b8;
        font-size: 0.8rem;
    }
    .dropdown-section:not(:last-child) {
        border-bottom: 1px solid #f1f5f9;
    }
`;
document.head.appendChild(style);
