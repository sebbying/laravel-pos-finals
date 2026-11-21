document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!confirm(form.dataset.confirm))event.preventDefault();}));
document.querySelector('[data-print]')?.addEventListener('click',()=>window.print());
const product=document.getElementById('product'),quantity=document.getElementById('quantity');
function updateSummary(){const option=product?.selectedOptions[0];if(!option)return;const price=option.dataset.price||'0.00';const parts=price.split('.');const cents=Number(parts[0])*100+Number((parts[1]||'').padEnd(2,'0'));const qty=Math.max(0,Number(quantity.value)||0);document.getElementById('total').textContent=new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP'}).format(cents*qty/100);document.getElementById('stock-label').textContent=option.dataset.stock?option.dataset.stock+' units currently available.':'Choose a product to see available stock.';}
product?.addEventListener('change',updateSummary);quantity?.addEventListener('input',updateSummary);if(product)updateSummary();
document.getElementById('checkout')?.addEventListener('submit',function(){this.querySelector('button').disabled=true;});
