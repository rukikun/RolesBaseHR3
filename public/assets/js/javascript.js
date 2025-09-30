async function clockIn(employeeId) {
    try {
        const response = await fetch('/api/clock-in', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                employee_id: employeeId,
                clock_in: new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' })
            })
        });

        if (!response.ok) throw new Error('Clock-in failed');

        const result = await response.json();
        if (result.success) {
            alert('Clocked in successfully!');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message);
    }
}
