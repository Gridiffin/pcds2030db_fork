# Fix Card Title CSS Inheritance Issue

## Problem
The `.card-header .card-title` selector in `view-programs.css` is too generic and affecting all card titles across the project, including the h5 card titles in other pages.

## Root Cause
Generic selector `html body.agency-layout.page-loaded div.d-flex.flex-column.min-vh-100 div.content-wrapper.agency-content main.flex-fill div.card.shadow-sm div.card-header.d-flex.justify-content-between.align-items-center h5.card-title.m-0` is being affected by:
```css
.card-header .card-title {
    font-weight: 600;
    color: #2c3e50;
}
```

## Solution
- [ ] Scope card title styles to program cards only
- [ ] Update CSS to be specific to draft/finalized program cards
- [ ] Test to ensure no regression on program cards
- [ ] Verify other card titles return to normal styling

## Files to Modify
- `assets/css/pages/view-programs.css`

## Expected Outcome
- Program card titles maintain enhanced styling
- Other card titles use default styling
