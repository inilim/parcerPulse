<script>
function forEach (iterable, callback)
{
   if(typeof callback !== 'function')
   {
      throw 'forEach: callback not function.';
   }
   if(typeof iterable !== 'object')
   {
      throw 'forEach: value not iterable.';
   }

   if(iterable.constructor.name === 'Array')
   {
      iterable.forEach(function(value, index)
      {
         callback(value, index);
      });
   }
   else if(iterable.constructor.name === 'Object')
   {
      for(let [key, value] of Object.entries(iterable))
      {
         callback(value, key);
      }
   }
   else
   {
      throw 'forEach: value not Array or Object.';
   }
}

let mainData = '<?=$json?>';
mainData = JSON.parse(mainData);
let conteiner = document.querySelector('div.cols');
let hidden = conteiner.querySelector('div.col');
let clone = hidden.cloneNode(true);

hidden.remove();
clone.removeAttribute('style');

document.addEventListener('DOMContentLoaded', function()
{
   genPage(mainData);
});

function genPage (profiles)
{
   forEach(profiles, function(value, index)
   {
      genCard(index, value);
   });
}

function genCard (id, instrs)
{
   clone = clone.cloneNode(true);
   clone.querySelector('.back > div.inner > p').innerText = textBack(instrs);
   clone.querySelector('.front > div.inner > p').innerText = 'User #' + id;
   clone.querySelector('.front > div.inner > span').innerText = 'Profit: ' + profitCalc(instrs) + '%';
   clone.querySelector('.front > div.inner > span').innerText += `\nStocks: ` + instrs.length;

   clone.querySelector('.front').style = generateGrad();
   conteiner.append(clone);
}

function profitCalc (instrs)
{
   let percent = 0;
   forEach(instrs, function(val)
   {
      percent = percent + parseFloat(val.diff_percent);
   });
   return percent.toFixed(2);
}

function textBack (instrs)
{
   instrs = instrs.slice(0,8);
   let text = `Changes in three days:\n\n`;
   text += `Stock | Profit | Price\n`;
   forEach(instrs, function(val)
   {
      val.diff_percent = parseFloat(val.diff_percent).toFixed(2);
      val.diff_price = parseFloat(val.diff_price).toFixed(2);
      text += `${val.ticker} | ${val.diff_percent}% | ${val.diff_price} ${val.currency}\n`;
   });
   return text;
}

function randomColor ()
{
   let hexString = "0123456789abcdef";
   let hexCode = "#";
   for( i=0; i<6; i++){
      hexCode += hexString[Math.floor(Math.random() * hexString.length)];
   }
   return hexCode;
}

function generateGrad ()
{
   let colorOne = randomColor();
   let colorTwo = randomColor();
   let angle = Math.floor(Math.random() * 360);
   return `background: linear-gradient(${angle}deg, ${colorOne}, ${colorTwo});`;
}
</script>