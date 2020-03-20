$(document).ready(function(){function t(i,s){this.x=i,this.y=s,this.Length=function(){return Math.sqrt(this.SqrLength())},this.SqrLength=function(){return this.x*this.x+this.y*this.y},this.Equals=function(t,i){return t.x==i.x&&t.y==i.y},this.Add=function(t){this.x+=t.x,this.y+=t.y},this.Sub=function(t){this.x-=t.x,this.y-=t.y},this.Div=function(t){this.x/=t,this.y/=t},this.Mul=function(t){this.x*=t,this.y*=t},this.Normalize=function(){var t=this.SqrLength();if(0!=t){var i=1/Math.sqrt(t);this.x*=i,this.y*=i}},this.Normalized=function(){var i=this.SqrLength();if(0!=i){var s=1/Math.sqrt(i);return new t(this.x*s,this.y*s)}return new t(0,0)}}function i(i,s,o,h){this.position=new t(i,s),this.mass=o,this.drag=h,this.force=new t(0,0),this.velocity=new t(0,0),this.AddForce=function(t){this.force.Add(t)},this.Integrate=function(i){var s=this.CurrentForce(this.position);s.Div(this.mass);var o=new t(this.velocity.x,this.velocity.y);o.Mul(i),this.position.Add(o),s.Mul(i),this.velocity.Add(s),this.force=new t(0,0)},this.CurrentForce=function(){var i=new t(this.force.x,this.force.y),s=this.velocity.Length(),o=new t(this.velocity.x,this.velocity.y);return o.Mul(this.drag*this.mass*s),i.Sub(o),i}}function s(i,o){this.pos=new t(i,o),this.rotationSpeed=600*Math.random()+800,this.angle=e*Math.random()*360,this.rotation=e*Math.random()*360,this.cosA=1,this.size=5,this.oscillationSpeed=1.5*Math.random()+.5,this.xSpeed=40,this.ySpeed=60*Math.random()+50,this.corners=new Array,this.time=Math.random();var h=Math.round(Math.random()*(r.length-1));this.frontColor=r[h][0],this.backColor=r[h][1];for(var n=0;4>n;n++){var a=Math.cos(this.angle+e*(90*n+45)),c=Math.sin(this.angle+e*(90*n+45));this.corners[n]=new t(a,c)}this.Update=function(t){this.time+=t,this.rotation+=this.rotationSpeed*t,this.cosA=Math.cos(e*this.rotation),this.pos.x+=Math.cos(this.time*this.oscillationSpeed)*this.xSpeed*t,this.pos.y+=this.ySpeed*t,this.pos.y>s.bounds.y&&(this.pos.x=Math.random()*s.bounds.x,this.pos.y=0)},this.Draw=function(t){t.fillStyle=this.cosA>0?this.frontColor:this.backColor,t.beginPath(),t.moveTo(this.pos.x+this.corners[0].x*this.size,this.pos.y+this.corners[0].y*this.size*this.cosA);for(var i=1;4>i;i++)t.lineTo(this.pos.x+this.corners[i].x*this.size,this.pos.y+this.corners[i].y*this.size*this.cosA);t.closePath(),t.fill()}}function o(s,h,n,a,c,l,p,y){this.particleDist=a,this.particleCount=n,this.particleMass=p,this.particleDrag=y,this.particles=new Array;var d=Math.round(Math.random()*(r.length-1));this.frontColor=r[d][0],this.backColor=r[d][1],this.xOff=Math.cos(e*l)*c,this.yOff=Math.sin(e*l)*c,this.position=new t(s,h),this.prevPosition=new t(s,h),this.velocityInherit=2*Math.random()+4,this.time=100*Math.random(),this.oscillationSpeed=2*Math.random()+2,this.oscillationDistance=40*Math.random()+40,this.ySpeed=40*Math.random()+80;for(var u=0;u<this.particleCount;u++)this.particles[u]=new i(s,h-u*this.particleDist,this.particleMass,this.particleDrag);this.Update=function(i){var s=0;this.time+=i*this.oscillationSpeed,this.position.y+=this.ySpeed*i,this.position.x+=Math.cos(this.time)*this.oscillationDistance*i,this.particles[0].position=this.position;var h=this.prevPosition.x-this.position.x,n=this.prevPosition.y-this.position.y,e=Math.sqrt(h*h+n*n);for(this.prevPosition=new t(this.position.x,this.position.y),s=1;s<this.particleCount;s++){var r=t.Sub(this.particles[s-1].position,this.particles[s].position);r.Normalize(),r.Mul(e/i*this.velocityInherit),this.particles[s].AddForce(r)}for(s=1;s<this.particleCount;s++)this.particles[s].Integrate(i);for(s=1;s<this.particleCount;s++){var a=new t(this.particles[s].position.x,this.particles[s].position.y);a.Sub(this.particles[s-1].position),a.Normalize(),a.Mul(this.particleDist),a.Add(this.particles[s-1].position),this.particles[s].position=a}this.position.y>o.bounds.y+this.particleDist*this.particleCount&&this.Reset()},this.Reset=function(){this.position.y=-Math.random()*o.bounds.y,this.position.x=Math.random()*o.bounds.x,this.prevPosition=new t(this.position.x,this.position.y),this.velocityInherit=2*Math.random()+4,this.time=100*Math.random(),this.oscillationSpeed=2*Math.random()+1.5,this.oscillationDistance=40*Math.random()+40,this.ySpeed=40*Math.random()+80;var s=Math.round(Math.random()*(r.length-1));this.frontColor=r[s][0],this.backColor=r[s][1],this.particles=new Array;for(var h=0;h<this.particleCount;h++)this.particles[h]=new i(this.position.x,this.position.y-h*this.particleDist,this.particleMass,this.particleDrag)},this.Draw=function(i){for(var s=0;s<this.particleCount-1;s++){var o=new t(this.particles[s].position.x+this.xOff,this.particles[s].position.y+this.yOff),h=new t(this.particles[s+1].position.x+this.xOff,this.particles[s+1].position.y+this.yOff);this.Side(this.particles[s].position.x,this.particles[s].position.y,this.particles[s+1].position.x,this.particles[s+1].position.y,h.x,h.y)<0?(i.fillStyle=this.frontColor,i.strokeStyle=this.frontColor):(i.fillStyle=this.backColor,i.strokeStyle=this.backColor),0==s?(i.beginPath(),i.moveTo(this.particles[s].position.x,this.particles[s].position.y),i.lineTo(this.particles[s+1].position.x,this.particles[s+1].position.y),i.lineTo(.5*(this.particles[s+1].position.x+h.x),.5*(this.particles[s+1].position.y+h.y)),i.closePath(),i.stroke(),i.fill(),i.beginPath(),i.moveTo(h.x,h.y),i.lineTo(o.x,o.y),i.lineTo(.5*(this.particles[s+1].position.x+h.x),.5*(this.particles[s+1].position.y+h.y)),i.closePath(),i.stroke(),i.fill()):s==this.particleCount-2?(i.beginPath(),i.moveTo(this.particles[s].position.x,this.particles[s].position.y),i.lineTo(this.particles[s+1].position.x,this.particles[s+1].position.y),i.lineTo(.5*(this.particles[s].position.x+o.x),.5*(this.particles[s].position.y+o.y)),i.closePath(),i.stroke(),i.fill(),i.beginPath(),i.moveTo(h.x,h.y),i.lineTo(o.x,o.y),i.lineTo(.5*(this.particles[s].position.x+o.x),.5*(this.particles[s].position.y+o.y)),i.closePath(),i.stroke(),i.fill()):(i.beginPath(),i.moveTo(this.particles[s].position.x,this.particles[s].position.y),i.lineTo(this.particles[s+1].position.x,this.particles[s+1].position.y),i.lineTo(h.x,h.y),i.lineTo(o.x,o.y),i.closePath(),i.stroke(),i.fill())}},this.Side=function(t,i,s,o,h,n){return(t-s)*(n-o)-(i-o)*(h-s)}}var h=30,n=1/h,e=Math.PI/180,r=(180/Math.PI,[["#84CCF8","#ADDDFA"],["#D6EEFC","#458DB8"],["#2E5E7B","#172F3D"],["#84CCF8","#ADDDFA"]]);t.Lerp=function(i,s,o){return new t((s.x-i.x)*o+i.x,(s.y-i.y)*o+i.y)},t.Distance=function(i,s){return Math.sqrt(t.SqrDistance(i,s))},t.SqrDistance=function(t,i){var s=t.x-i.x,o=t.y-i.y;return s*s+o*o+z*z},t.Scale=function(i,s){return new t(i.x*s.x,i.y*s.y)},t.Min=function(i,s){return new t(Math.min(i.x,s.x),Math.min(i.y,s.y))},t.Max=function(i,s){return new t(Math.max(i.x,s.x),Math.max(i.y,s.y))},t.ClampMagnitude=function(i,s){var o=i.Normalized;return new t(o.x*s,o.y*s)},t.Sub=function(i,s){return new t(i.x-s.x,i.y-s.y,i.z-s.z)},s.bounds=new t(0,0),o.bounds=new t(0,0),a={},a.Context=function(i){var e=0,r=document.getElementById(i),c=document.createElement("canvas");c.width=r.offsetWidth,c.height=r.offsetHeight,r.appendChild(c);var l=c.getContext("2d"),p=7,y=30,d=8,u=8,f=new Array;for(o.bounds=new t(c.width,c.height),e=0;p>e;e++)f[e]=new o(Math.random()*c.width,-Math.random()*c.height*2,y,d,u,45,1,.05);var x=25,M=new Array;for(s.bounds=new t(c.width,c.height),e=0;x>e;e++)M[e]=new s(Math.random()*c.width,Math.random()*c.height);this.resize=function(){c.width=r.offsetWidth,c.height=r.offsetHeight,s.bounds=new t(c.width,c.height),o.bounds=new t(c.width,c.height)},this.start=function(){this.stop();this.interval=setInterval(function(){a.update()},1e3/h)},this.stop=function(){clearInterval(this.interval)},this.update=function(){var t=0;for(l.clearRect(0,0,c.width,c.height),t=0;x>t;t++)M[t].Update(n),M[t].Draw(l);for(t=0;p>t;t++)f[t].Update(n),f[t].Draw(l)}};var a=new a.Context("confetti");a.start(),$(window).resize(function(){a.resize()})});