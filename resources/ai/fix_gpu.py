import os
import shutil
import glob
import site

# Mencari lokasi otomatis tempat Pip menginstal library Anda
user_site = site.getusersitepackages()

# Targetkan semua file .dll di dalam ekosistem NVIDIA
nvidia_dlls = glob.glob(os.path.join(user_site, "nvidia", "**", "bin", "*.dll"), recursive=True)

# Lokasi markas besar ONNX Runtime
ort_capi = os.path.join(user_site, "onnxruntime", "capi")

print("="*50)
print("MEMULAI PROSES PENYATUAN EKOSISTEM NVIDIA GPU")
print("="*50)

if not os.path.exists(ort_capi):
    print(f"❌ Folder ONNX tidak ditemukan di: {ort_capi}")
    print("Pastikan Anda sudah menjalankan: pip install onnxruntime-gpu")
else:
    print(f"🔎 Menemukan {len(nvidia_dlls)} file komponen NVIDIA.")
    print("⏳ Sedang memindahkan ke markas ONNX Runtime...\n")
    
    berhasil = 0
    for dll in nvidia_dlls:
        try:
            shutil.copy(dll, ort_capi)
            berhasil += 1
        except Exception as e:
            pass
            
    print(f"✔️ {berhasil} file berhasil disatukan!")
    print("\n✅ PROSES SELESAI!")
    print("Sekarang, Anda bisa menjalankan server AI Anda dengan tenang.")