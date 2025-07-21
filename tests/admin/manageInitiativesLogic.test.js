// Unit tests for admin initiatives JS logic
const { formatDate, renderTable } = require('../../../assets/js/admin/initiatives/manageInitiatives');

describe('formatDate', () => {
  it('formats valid date strings', () => {
    expect(formatDate('2024-06-18')).toMatch(/Jun.*18.*2024/);
  });
  it('returns empty string for invalid date', () => {
    expect(formatDate('not-a-date')).toBe('');
  });
});

describe('renderTable', () => {
  const columns = {
    id: 'id',
    name: 'name',
    number: 'number',
    description: 'description',
    start_date: 'start_date',
    end_date: 'end_date',
    is_active: 'is_active',
  };
  it('renders no initiatives message', () => {
    expect(renderTable([], columns)).toMatch(/No initiatives found/);
  });
  it('renders a table row for each initiative', () => {
    const initiatives = [
      { id: 1, name: 'Test', number: 'A1', description: 'Desc', start_date: '2024-06-18', end_date: '2024-07-01', is_active: 1, program_count: 2 },
    ];
    expect(renderTable(initiatives, columns)).toMatch(/Test/);
    expect(renderTable(initiatives, columns)).toMatch(/A1/);
  });
}); 