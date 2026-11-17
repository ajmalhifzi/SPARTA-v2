import fs from 'node:fs';
import path from 'node:path';
const names = ['file-description','id','file-pencil','book-2','user-square-rounded','calendar','notes','clipboard-check','folders','menu-2','logout','circle-check','alert-circle','printer','device-floppy','lock','check','paperclip','chevron-down','info-circle','arrow-left','arrow-right','arrow-up-right','plus','search','eye'];
const symbols = names.map(name => {
    const svg=fs.readFileSync(path.resolve('node_modules/@tabler/icons/icons/outline', name+'.svg'),'utf8');
    const contents=svg.slice(svg.indexOf('>')+1,svg.lastIndexOf('</svg>'));
    return `<symbol id="${name}" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">${contents}</g></symbol>`;
});
fs.writeFileSync('public/icons.svg','<svg xmlns="http://www.w3.org/2000/svg">'+symbols.join('')+'</svg>');
console.log(`Bundled ${names.length} Tabler icons.`);
