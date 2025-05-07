import qrcode
import sys

# Lire le contenu depuis la ligne de commande
if len(sys.argv) < 2:
    print("Erreur : aucun contenu reçu.")
    sys.exit(1)

data = sys.argv[1]

qr = qrcode.QRCode(
    version=1,
    error_correction=qrcode.constants.ERROR_CORRECT_L,
    box_size=10,
    border=4,
)
qr.add_data(data)
qr.make(fit=True)

img = qr.make_image(fill_color="black", back_color="white")
img.save(r"C:\xampp\htdocs\fpjw\projet_web\front_office\front_office\model\qrcode.png")