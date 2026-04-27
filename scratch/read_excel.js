const xlsx = require('xlsx');

function dumpHeaders(filePath) {
    console.log(`\nReading file: ${filePath}`);
    const workbook = xlsx.readFile(filePath);
    
    workbook.SheetNames.forEach(sheetName => {
        console.log(`\nSheet: ${sheetName}`);
        const sheet = workbook.Sheets[sheetName];
        const data = xlsx.utils.sheet_to_json(sheet, { header: 1 });
        if (data.length > 0) {
            // Check first few rows for headers to avoid empty tops
            let headerRowIndex = 0;
            let maxCols = 0;
            for (let i = 0; i < Math.min(10, data.length); i++) {
                if (data[i]) {
                    const colCount = data[i].filter(c => c !== undefined && c !== null && c !== '').length;
                    if (colCount > maxCols) {
                        maxCols = colCount;
                        headerRowIndex = i;
                    }
                }
            }
            console.log("HEADERS:", data[headerRowIndex].filter(h => h !== undefined && h !== null && h !== '').join(' | '));
            if (data.length > headerRowIndex + 1) {
                console.log("ROW 1  :", data[headerRowIndex + 1].filter(h => h !== undefined && h !== null && h !== '').join(' | '));
            }
        } else {
            console.log('Empty sheet');
        }
    });
}

dumpHeaders('c:/xampp/htdocs/mjsir_tracker/frontend/src/assets/MJSIR_MANUSCRIPT_S  T A T U S  (12).xlsx');
dumpHeaders('c:/xampp/htdocs/mjsir_tracker/frontend/src/assets/Prescreening Monitoring Sheet_v2026.xlsx');
