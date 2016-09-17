$(window).load(function() {
$('#gallery img').each(function() {
createCanvas(this);
});
function createCanvas(image) {
var canvas = document.createElement('canvas');
if (canvas.getContext) {
var ctx = canvas.getContext("2d");

	      // Определяем размер элемента canvas
	      canvas.width = image.width;
	      canvas.height = image.height;

	      // Как только мы получили объект исходного изображения, можно использовать метод drawImage(reference, x, y) для вывода его в элемент canvas. 
		  // x, y - координаты, где должно размещаться изображение.
	      ctx.drawImage(image, 0, 0);

	      // Получаем данные изображения и сохраняем его в массиве imageData. 
		  // Данные о точках получаем с помощью метода getImageData(). 
		  // Данные включают цвет точки (десятичное, RGB значение) и прозрачность (значение альфа канала).
		  // Каждый цвет представлен целым значением в диапазоне 0 и 255. 
		  // imageData.da содержит данных объемом (высота x width x 4) байт с индексом в диапазоне от 0 до (высота x ширина x 4)-1.
	      var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height),
	          pixelData = imageData.data;

	      // Цикл по всем точкам в массиве imageData 
	      // и модификация значений цветов.
	      for (var y = 0; y < canvas.height; y++) {
	        for (var x = 0; x < canvas.width; x++) {

	          // Вычисляем индекс нужной точки (x,y):
	          var i = (y * 4 * canvas.width) + (x * 4);

	          // Получаем значение RGB.
	          var red = pixelData[i];
	          var green = pixelData[i + 1];
	          var blue = pixelData[i + 2];

	          // Переводим цвет в серую шкалу. Одна из формул конвертации:   
	          var grayScale = (red * 0.3) + (green * 0.59) + (blue * .11);

	          pixelData[i] = grayScale;
	          pixelData[i + 1] = grayScale;
	          pixelData[i + 2] = grayScale;
	        }
	      }

	      // Помещаем модифицированные данные imageData обратно в элемент canvas.
	      ctx.putImageData(imageData, 0, 0, 0, 0, imageData.width, imageData.height);

	      // Вставляем элемент canvas в DOM, перед изображением:
	      image.parentNode.insertBefore(canvas, image);
	    }
	  }
	});