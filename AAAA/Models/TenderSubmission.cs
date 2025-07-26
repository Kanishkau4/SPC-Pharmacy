namespace AAAA.Models
{
    public class TenderSubmission
    {
        public int TenderId { get; set; }
        public string SupplierName { get; set; }
        public decimal Price { get; set; }
        public int DeliveryTime { get; set; }
        public DateTime SubmissionDate { get; set; } = DateTime.Now;  // Optional, since it's defaulted in SQL
        public string Status { get; set; }
    }
}
