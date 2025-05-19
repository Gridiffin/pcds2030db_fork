# Program Ordering in Reports

## Overview

This guide explains how to control the order in which programs appear in generated reports using the new program ordering feature.

## How Program Ordering Works

By default, programs in reports were displayed alphabetically. With the new ordering feature, you can now specify the exact sequence in which programs should appear in your generated reports.

## Using the Program Ordering Feature

1. **Select a reporting period and sector** from the dropdowns in the Generate Reports page
2. **Select the programs** you want to include in your report by checking the checkboxes
3. **Assign order numbers** using the number inputs that appear next to each selected program
   - Lower numbers will appear first in the report (1 comes before 2, etc.)
   - You can leave gaps in the numbering if needed (1, 3, 5, etc.)
   - Duplicate numbers will be automatically corrected when generating the report

## Special Features

### Select All Button
When you click "Select All", all programs for the currently visible sector(s) will be selected and automatically assigned sequential order numbers.

### Sort Numerically Button
The "Sort Numerically" button helps you reorganize your order numbers:
- It will resequence all your selected programs based on their current order values
- This creates a clean, sequential numbering (1, 2, 3, 4, etc.) without gaps
- Programs with the same number will be resolved by assigning new, sequential numbers

### Automatic Order Assignment
When you check a program's checkbox, it will automatically be assigned the next available number.

## Examples

### Example 1: Basic Ordering
1. Select reporting period "Q2 2023"
2. Select sector "Forestry"
3. Check the box next to "Forest Conservation Program" and assign order number "1"
4. Check the box next to "Reforestation Initiative" and assign order number "2"
5. Generate the report - programs will appear in the order you specified

### Example 2: Changing the Order Mid-Process
1. Follow steps in Example 1
2. Decide you want to switch the order
3. Change "Forest Conservation Program" to order number "2"
4. Change "Reforestation Initiative" to order number "1" 
5. Click "Sort Numerically" to clean up any duplicate numbering
6. Generate the report - programs will now appear in the new order

## Best Practices

1. **Use sequential numbers** without large gaps for clarity
2. **Use the "Sort Numerically" button** before generating reports to ensure clean ordering
3. **Consider the narrative flow** of your report when ordering programs
4. **Filter by sector** to more easily manage the ordering of related programs

## Troubleshooting

If you encounter any issues with program ordering:

1. **Clear all selections** and start over by clicking "Deselect All"
2. **Refresh the page** if the order inputs are not appearing correctly
3. **Ensure all selected programs have number values** - blank values may cause unexpected ordering
4. **Use the "Sort Numerically" button** to resolve any duplicate or invalid ordering
5. **Check the program count badge** - it shows how many programs are currently selected
6. **Verify program selection after changing sectors** - selecting a different sector will uncheck programs from other sectors

### Common Issues

1. **Duplicate numbers**: If you assign the same number to multiple programs, the system will automatically resolve this when you generate the report or click "Sort Numerically". Programs will appear in the order you set.

2. **Missing order numbers**: If you check a program but don't assign an order number, it will be assigned the next available number automatically.

3. **Long program names**: If program names appear truncated in the UI, hover over them to see the full name in a tooltip.

For additional assistance, contact the system administrator.
