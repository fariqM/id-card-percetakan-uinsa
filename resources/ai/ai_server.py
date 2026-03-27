from flask import Flask, request, jsonify
from rembg import remove
from PIL import Image
import io
import base64

app = Flask(__name__)

# Warna Merah Standar Pasfoto
BG_COLOR = (192, 0, 0, 255) 

@app.route('/process-bg', methods=['POST'])
def process_background():
    try:
        # 1. Terima gambar base64 dari Laravel
        data = request.json
        image_data = data['image'].split(',')[1]
        
        # 2. Decode gambar menjadi format yang bisa dibaca mesin
        img_bytes = base64.b64decode(image_data)
        input_image = Image.open(io.BytesIO(img_bytes)).convert("RGBA")
        
        # 3. Proses hapus background dengan AI (menggunakan tenaga CPU murni)
        output_image = remove(input_image)
        
        # 4. Buat kanvas merah baru
        red_bg = Image.new("RGBA", output_image.size, BG_COLOR)
        
        # 5. Tempelkan objek (wajah/tubuh) ke atas kanvas merah
        red_bg.paste(output_image, (0, 0), output_image)
        
        # 6. Convert kembali ke RGB agar bisa disimpan sebagai JPG nantinya
        final_image = red_bg.convert("RGB")
        
        # 7. Encode kembali ke base64 untuk dikembalikan ke Laravel
        buffered = io.BytesIO()
        final_image.save(buffered, format="JPEG", quality=95)
        img_str = base64.b64encode(buffered.getvalue()).decode("utf-8")
        
        return jsonify({
            'success': True,
            'image': 'data:image/jpeg;base64,' + img_str
        })

    except Exception as e:
        # Jika ada error, kirim pesan error kembali ke Laravel
        return jsonify({'success': False, 'message': str(e)}), 500

if __name__ == '__main__':
    # Menjalankan server AI di port 5000
    app.run(host='127.0.0.1', port=5000)