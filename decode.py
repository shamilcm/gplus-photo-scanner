import glob
from os import path
import stepic
import Image


# Function to decode the stego message from image
def  decode(filepath):
	img = Image.open(filepath)
	if(img.mode!='L'):
		message = stepic.decode(img)
		return message
	return "  "

#Function to check if a message is a valid msg
def isvalidformat(msg):
	if ((msg[0]=='0' or msg[0]=='1')) and (msg[1] >= '0' and msg[1] <= '9') :
		return True
	else:
		return False

#Main Function
encode_dir = path.expanduser("images/")
for infile in glob.glob(encode_dir+"*.jpg"):
	msg = decode(infile)
	if isvalidformat(msg):
		print infile
		print "-->DECODE RUN : ",msg
