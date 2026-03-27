import torch
print(f"PyTorch version: {torch.__version__}")
print(f"CUDA available: {torch.cuda.is_available()}")
print(f"Cuda version: {torch.version.cuda}")
print(f"Number of GPUs: {torch.cuda.device_count()}")