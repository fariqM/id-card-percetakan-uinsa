import cv2
import sys
import os
import numpy as np
import mediapipe as mp

def main():
    # Mengambil NIM dari argumen yang dikirim Laravel
    if len(sys.argv) < 2:
        print("Error: NIM tidak diberikan")
        return
    
    nim = sys.argv[1]
    save_path = f"public/storage/foto_ktm/{nim}.jpg"
    
    # Inisialisasi MediaPipe untuk ganti background
    mp_selfie = mp.solutions.selfie_segmentation
    cap = cv2.VideoCapture(0) # Gunakan ID 0 untuk webcam atau ID lain untuk DSLR

    print(f"Memulai kamera untuk NIM: {nim}. Tekan 'SPACE' untuk potret.")

    with mp_selfie.SelfieSegmentation(model_selection=1) as selfie_segmentation:
        while cap.isOpened():
            ret, frame = cap.read()
            if not ret: break

            # Flip frame agar seperti cermin
            frame = cv2.flip(frame, 1)
            rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)

            # Proses segmentasi
            results = selfie_segmentation.process(rgb_frame)
            condition = np.stack((results.segmentation_mask,) * 3, axis=-1) > 0.1

            # Buat background merah (Sesuai Pedoman Akademik UINSA)
            red_bg = np.zeros(frame.shape, dtype=np.uint8)
            red_bg[:] = (0, 0, 255) # BGR: Merah

            # Gabungkan foto dengan background merah
            output_image = np.where(condition, frame, red_bg)

            # Tambahkan Overlay panduan posisi (Patern ukuran sesuai KTM)
            cv2.rectangle(output_image, (200, 50), (440, 430), (0, 255, 0), 2)
            cv2.putText(output_image, "Posisikan Wajah di Kotak Hijau", (150, 30), 
                        cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)

            cv2.imshow('Layanan Foto KTM UINSA - Press SPACE to Capture', output_image)

            key = cv2.waitKey(1)
            if key % 256 == 32: # SPACE dipencet
                # Simpan hasil foto
                if not os.path.exists('public/storage/foto_ktm'):
                    os.makedirs('public/storage/foto_ktm')
                
                # Crop otomatis ke rasio 3x4 sebelum simpan
                final_crop = output_image[0:480, 160:520] # Contoh koordinat crop 3x4
                cv2.imwrite(save_path, final_crop)
                print(f"Foto berhasil disimpan di: {save_path}")
                break
            elif key % 256 == 27: # ESC untuk batal
                print("Proses dibatalkan.")
                break

    cap.release()
    cv2.destroyAllWindows()

if __name__ == "__main__":
    main()