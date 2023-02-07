<script>
let mainData = '<?=$json?>';
mainData = JSON.parse(mainData);
//console.log(mainData);
let conteiner = document.querySelector('div.cols');
let hidden = conteiner.querySelector('div.col');
let clone = hidden.cloneNode(true);

hidden.remove();
clone.removeAttribute('style');

document.addEventListener('DOMContentLoaded', function()
{
   genPage(mainData);
   console.log(mainData.length);
});

function genPage (profiles)
{
   profiles.forEach(function(instrs)
   {
      instrs = Object.entries(instrs);
      
      //console.log(instrs);
      genCard(instrs[0][1].id_profile, instrs);
   });
}

function genCard (id, instrs)
{
   //console.log(instrs);
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
   instrs.forEach(function(val)
   {
      percent = percent + val[1].diff_percent;
   });
   return percent.toFixed(2);
}

function textBack (instrs)
{
   let text = `Changes in three days:\n\n`;
   text += `Stock | Profit | Price\n`;
   instrs.forEach(function(val)
   {
      text += `${val[1].ticker} | ${val[1].diff_percent}% | ${val[1].diff_price} ${val[1].currency}\n`;
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